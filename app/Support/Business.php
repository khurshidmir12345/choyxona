<?php

namespace App\Support;

use App\Casts\BusinessType;
use App\Models\Company;

/**
 * Joriy foydalanuvchi kompaniyasining biznes turi.
 * So'rov davomida bir marta o'qiladi (once), testlar orasida tozalanadi.
 */
class Business
{
    public static function current(): BusinessType
    {
        return once(function () {
            $companyId = auth()->user()?->companyId();

            // value() cast qo'llaydi — enum yoki null qaytadi.
            $value = $companyId
                ? Company::query()->whereKey($companyId)->value('business_type')
                : null;

            if ($value instanceof BusinessType) {
                return $value;
            }

            return BusinessType::tryFrom((string) $value) ?? BusinessType::Cafe;
        });
    }

    /** Kompaniya turi tanlanganmi (null bo'lsa — o'rnatish ekrani). */
    public static function isChosen(): bool
    {
        $companyId = auth()->user()?->companyId();

        if (! $companyId) {
            return true;
        }

        return filled(Company::query()->whereKey($companyId)->value('business_type'));
    }

    public static function isCafe(): bool
    {
        return self::current() === BusinessType::Cafe;
    }

    public static function isRetail(): bool
    {
        return self::current() === BusinessType::Retail;
    }

    public static function term(string $key): string
    {
        return self::current()->term($key);
    }

    /** Tur o'zgartirilganda keshni tozalash. */
    public static function forget(): void
    {
        \Illuminate\Support\Once::flush();
    }
}
