<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Bot webhook'i: /start ga mini ilovani ochadigan tugma yuboradi.
 * Boshqa xabarlar e'tiborsiz — bot faqat kassaga kirish eshigi.
 */
class WebhookController extends Controller
{
    public function __invoke(Request $request, TelegramService $telegram): Response
    {
        $secret = Setting::get(TelegramService::SECRET_KEY);

        if ($secret && ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return response('forbidden', 403);
        }

        $message = $request->input('message') ?? $request->input('edited_message');
        $chatId = $message['chat']['id'] ?? null;
        $text = (string) ($message['text'] ?? '');

        if ($chatId && ($text === '' || str_starts_with($text, '/start') || str_starts_with($text, '/kassa'))) {
            $telegram->sendMessage($chatId, "<b>Choyxona POS</b>\nKassani ochish uchun pastdagi tugmani bosing.", [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => '🧾 Kassani ochish', 'web_app' => ['url' => route('telegram.entry')]],
                    ]],
                ],
            ]);
        }

        return response('ok');
    }
}
