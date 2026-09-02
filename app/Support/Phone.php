<?php

namespace App\Support;

/** Telefon raqamlari bazada bir xil ko'rinishda: +998901234567. */
class Phone
{
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9) {
            $digits = '998'.$digits;
        }

        return '+'.$digits;
    }
}
