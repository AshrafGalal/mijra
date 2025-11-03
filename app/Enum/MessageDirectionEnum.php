<?php

namespace App\Enum;

enum MessageDirectionEnum: int
{
    case INCOMING = 1;
    case OUTGOING = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
