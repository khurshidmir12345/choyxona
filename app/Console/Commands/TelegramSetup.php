<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Telegram botni ulash: tokenni bazaga yozadi, webhook va menyu tugmasini
 * o'rnatadi. Serverda bir marta ishga tushiriladi:
 *   php artisan telegram:setup            — bazadagi token bilan
 *   php artisan telegram:setup <token>    — yangi token bilan
 * APP_URL https bo'lishi shart (Telegram http'ni qabul qilmaydi).
 */
class TelegramSetup extends Command
{
    protected $signature = 'telegram:setup {token? : Bot tokeni (BotFather bergan)}';

    protected $description = 'Telegram bot tokenini saqlaydi, webhook va mini ilova tugmasini o\'rnatadi';

    public function handle(TelegramService $telegram): int
    {
        if ($token = $this->argument('token')) {
            Setting::set(TelegramService::TOKEN_KEY, trim($token));
            $this->info('Token saqlandi.');
        }

        if (! $telegram->isConfigured()) {
            $this->error('Bot tokeni yo\'q. php artisan telegram:setup <token>');

            return self::FAILURE;
        }

        $me = $telegram->call('getMe');

        if (! ($me['ok'] ?? false)) {
            $this->error('Token noto\'g\'ri: '.($me['description'] ?? 'noma\'lum xato'));

            return self::FAILURE;
        }

        $this->info('Bot: @'.($me['result']['username'] ?? '?'));

        $entryUrl = route('telegram.entry');
        $webhookUrl = route('telegram.webhook');

        if (! str_starts_with($entryUrl, 'https://')) {
            $this->warn("APP_URL https emas ({$entryUrl}). Telegram faqat https'ni qabul qiladi — webhook o'rnatilmadi.");

            return self::FAILURE;
        }

        $secret = Setting::get(TelegramService::SECRET_KEY);

        if (! $secret) {
            $secret = Str::random(48);
            Setting::set(TelegramService::SECRET_KEY, $secret);
        }

        $this->report('Webhook', $telegram->call('setWebhook', [
            'url' => $webhookUrl,
            'secret_token' => $secret,
            'allowed_updates' => ['message'],
            'drop_pending_updates' => true,
        ]));

        $this->report('Menyu tugmasi', $telegram->call('setChatMenuButton', [
            'menu_button' => [
                'type' => 'web_app',
                'text' => 'Kassa',
                'web_app' => ['url' => $entryUrl],
            ],
        ]));

        $this->report('Buyruqlar', $telegram->call('setMyCommands', [
            'commands' => [
                ['command' => 'start', 'description' => 'Kassani ochish'],
            ],
        ]));

        return self::SUCCESS;
    }

    private function report(string $label, array $result): void
    {
        if ($result['ok'] ?? false) {
            $this->info("{$label}: o'rnatildi");
        } else {
            $this->error("{$label}: ".($result['description'] ?? 'xato'));
        }
    }
}
