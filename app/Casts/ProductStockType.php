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
}
