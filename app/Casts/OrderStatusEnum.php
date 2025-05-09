<?php

namespace App\Casts;

enum OrderStatusEnum: string
{
    case Opened = 'opened';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    case Done = 'done';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
