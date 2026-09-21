<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Telegram Bot API bilan ishlash. Bot tokeni bazadagi settings jadvalida
 * (telegram_bot_token) — .env'ga bog'liq emas, `php artisan telegram:setup`
 * bilan o'rnatiladi.
 */
class TelegramService
{
    public const TOKEN_KEY = 'telegram_bot_token';

    public const SECRET_KEY = 'telegram_webhook_secret';

    /** Mini ilova initData qancha vaqt amal qiladi (soniya). */
    public const INIT_DATA_TTL = 86400;

    public function token(): ?string
    {
        return Setting::get(self::TOKEN_KEY);
    }

    public function isConfigured(): bool
    {
        return filled($this->token());
    }

    /**
     * Bot API metodini chaqiradi. Xato bo'lsa ['ok' => false, 'description' => ...].
     *
     * @return array<string, mixed>
     */
    public function call(string $method, array $params = []): array
    {
        $token = $this->token();

        if (! $token) {
            return ['ok' => false, 'description' => 'Bot tokeni o\'rnatilmagan.'];
        }

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->post("https://api.telegram.org/bot{$token}/{$method}", $params);

            return $response->json() ?? ['ok' => false, 'description' => 'Bo\'sh javob.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    public function sendMessage(int|string $chatId, string $text, array $extra = []): array
    {
        return $this->call('sendMessage', $extra + [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * Mini ilovadan kelgan initData imzosini tekshiradi.
     * https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     *
     * @return array<string, mixed>|null tekshiruvdan o'tsa — maydonlar (user — massiv), aks holda null
     */
    public function validateInitData(?string $initData): ?array
    {
        $token = $this->token();

        if (! $token || blank($initData)) {
            return null;
        }

        parse_str($initData, $data);

        $hash = $data['hash'] ?? null;

        if (! is_string($hash) || $hash === '') {
            return null;
        }

        unset($data['hash']);
        ksort($data);

        $pairs = [];

        foreach ($data as $key => $value) {
            $pairs[] = $key.'='.(is_array($value) ? json_encode($value) : $value);
        }

        $secret = hash_hmac('sha256', $token, 'WebAppData', true);
        $expected = hash_hmac('sha256', implode("\n", $pairs), $secret);

        if (! hash_equals($expected, $hash)) {
            return null;
        }

        $authDate = (int) ($data['auth_date'] ?? 0);

        if ($authDate <= 0 || now()->timestamp - $authDate > self::INIT_DATA_TTL) {
            return null;
        }

        $data['user'] = isset($data['user']) && is_string($data['user'])
            ? (json_decode($data['user'], true) ?: [])
            : [];

        return $data;
    }

    /** Shu initData uchun test imzosi (testlar va lokal tekshiruv uchun). */
    public function signInitData(array $fields): string
    {
        $token = (string) $this->token();
        ksort($fields);

        $pairs = [];

        foreach ($fields as $key => $value) {
            $pairs[] = $key.'='.$value;
        }

        $secret = hash_hmac('sha256', $token, 'WebAppData', true);
        $fields['hash'] = hash_hmac('sha256', implode("\n", $pairs), $secret);

        return http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
    }
}
