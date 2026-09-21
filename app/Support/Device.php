<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Qurilma turi: telefon (Telegram mini ilova yoki mobil brauzer) yoki kompyuter.
 * Telefonda zal POS alohida mobil ko'rinishda ochiladi (/m/zal).
 * `?desktop=1` bilan har doim kompyuter ko'rinishi.
 */
class Device
{
    public static function isMobile(?Request $request = null): bool
    {
        $request ??= request();

        if ($request->boolean('desktop')) {
            return false;
        }

        if ($request->hasSession() && $request->session()->get('telegram_app')) {
            return true;
        }

        $ua = (string) $request->userAgent();

        return (bool) preg_match('/Android|iPhone|iPod|Windows Phone|Mobile/i', $ua)
            && ! preg_match('/iPad|Tablet/i', $ua);
    }
}
