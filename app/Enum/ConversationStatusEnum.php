<?php

namespace App\Enum;

enum ConversationStatusEnum: int
{
    case OPEN = 1;
    case CLOSED = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
