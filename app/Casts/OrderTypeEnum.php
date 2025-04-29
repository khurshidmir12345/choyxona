<?php

namespace App\Casts;

enum OrderTypeEnum : string
{
    case Delivery = 'delivery';
    case Takeaway = 'takeaway';
    case Cafe = 'cafe';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
