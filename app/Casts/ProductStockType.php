<?php

namespace App\Casts;

enum ProductStockType : string
{
    case Add = 'add';
    case Sell = 'sell';
    case Waste = 'waste';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Ombor jurnalidan qo'lda kiritish mumkin bo'lgan turlar.
     * "Sotildi" faqat buyurtma yopilganda avtomatik yoziladi.
     */
    public static function manualCases(): array
    {
        return [self::Add, self::Waste];
    }

    public static function manualValues(): array
    {
        return array_map(fn (self $c) => $c->value, self::manualCases());
    }

    public function label(): string
    {
        return match($this) {
            self::Add => 'Kirim',
            self::Sell => 'Chiqim (sotildi)',
            self::Waste => 'Chiqim (yo‘qotildi)',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Add => 'success',
            self::Sell => 'warning',
            self::Waste => 'danger',
        };
    }
}
