<?php

namespace App\Casts;

enum PlaceStatusEnum : string
{
    case Empty = 'empty';
    case Busy = 'busy';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
