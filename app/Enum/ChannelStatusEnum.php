<?php

namespace App\Enum;

enum ChannelStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::EXPIRED => 'Expired',
            self::REVOKED => 'Revoked',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'gray',
            self::EXPIRED => 'orange',
            self::REVOKED => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_combine(
            self::values(),
            array_map(fn($status) => $status->label(), self::cases())
        );
    }
}
