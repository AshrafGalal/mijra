<?php

namespace App\Enum;

enum MessageDirectionEnum: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';

    public function label(): string
    {
        return match ($this) {
            self::INBOUND => 'Inbound',
            self::OUTBOUND => 'Outbound',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

