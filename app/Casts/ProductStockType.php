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
