<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Telegram mini ilova orqali kirish.
 *
 * Bot tugmasi /tg sahifasini ochadi. Sahifa initData'ni /tg/auth ga yuboradi:
 * imzo to'g'ri va telegram_id biror xodimga bog'langan bo'lsa — darhol kiradi.
 * Bog'lanmagan bo'lsa sahifa telefon + parol so'raydi (/tg/login), muvaffaqiyatli
 * kirishda telegram_id xodimga yoziladi va keyingi safar parolsiz kiradi.
 */
class MiniAppController extends Controller
{
    public function __construct(private readonly TelegramService $telegram) {}

    public function entry(): View
    {
        return view('telegram.entry');
    }

    public function auth(Request $request): JsonResponse
    {
        $data = $this->telegram->validateInitData($request->input('init_data'));

        if (! $data) {
            return response()->json([
                'ok' => false,
                'outside' => true,
                'message' => 'Bu sahifa Telegram ilovasi ichida ochiladi.',
            ]);
        }

        $request->session()->put('telegram_app', true);

        $current = $request->user();

        if ($current instanceof User && $current->companyId() && $current->is_active) {
            $this->attachTelegram($current, $data['user']);

            return $this->enter($request, $current);
        }

        $telegramId = (int) ($data['user']['id'] ?? 0);

        $user = $telegramId
            ? User::query()->where('telegram_id', $telegramId)->first()
            : null;

        if (! $user || ! $user->companyId() || ! $user->is_active) {
            return response()->json([
                'ok' => false,
                'need_login' => true,
                'name' => $data['user']['first_name'] ?? null,
            ]);
        }

        $this->attachTelegram($user, $data['user']);

        return $this->enter($request, $user);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $this->telegram->validateInitData($request->input('init_data'));

        if (! $data) {
            return response()->json(['ok' => false, 'message' => 'Telegram ma\'lumotlari tasdiqlanmadi. Ilovani qayta oching.'], 422);
        }

        $request->validate([
            'phone_number' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $phone = Phone::normalize($request->input('phone_number'));
        $key = 'tg-login|'.$phone.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['ok' => false, 'message' => 'Juda ko\'p urinish. Bir necha daqiqadan keyin qayta urinib ko\'ring.'], 429);
        }

        $user = User::query()->where('phone_number', $phone)->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($key, 300);

            return response()->json(['ok' => false, 'message' => 'Telefon raqam yoki parol noto\'g\'ri.'], 422);
        }

        if (! $user->companyId()) {
            return response()->json(['ok' => false, 'message' => 'Hisobingiz hech qaysi choyxonaga bog\'lanmagan.'], 422);
        }

        if (! $user->is_active) {
            return response()->json(['ok' => false, 'message' => 'Hisobingiz o\'chirilgan. Rahbaringizga murojaat qiling.'], 422);
        }

        RateLimiter::clear($key);
        $request->session()->put('telegram_app', true);
        $this->attachTelegram($user, $data['user']);

        return $this->enter($request, $user);
    }

    private function attachTelegram(User $user, array $tgUser): void
    {
        $telegramId = (int) ($tgUser['id'] ?? 0);

        if (! $telegramId) {
            return;
        }

        // Bitta Telegram hisobi bitta xodimga: eski bog'lanish olib tashlanadi.
        User::query()
            ->where('telegram_id', $telegramId)
            ->whereKeyNot($user->id)
            ->update(['telegram_id' => null, 'telegram_username' => null]);

        $username = isset($tgUser['username']) ? mb_substr((string) $tgUser['username'], 0, 64) : null;

        if ($user->telegram_id !== $telegramId || $user->telegram_username !== $username) {
            $user->forceFill(['telegram_id' => $telegramId, 'telegram_username' => $username])->save();
        }
    }

    private function enter(Request $request, User $user): JsonResponse
    {
        if (! Auth::guard('web')->check() || Auth::guard('web')->id() !== $user->id) {
            Auth::guard('web')->login($user, remember: true);
        }

        $request->session()->regenerate();
        $request->session()->put('telegram_app', true);

        return response()->json([
            'ok' => true,
            'redirect' => route($user->homeRoute()),
            'name' => $user->name,
        ]);
    }
}
