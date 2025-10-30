<?php

namespace App\Enum;

enum MessageStatusEnum: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::READ => 'Read',
            self::FAILED => 'Failed',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => '⏳',
            self::SENT => '✓',
            self::DELIVERED => '✓✓',
            self::READ => '✓✓',
            self::FAILED => '❌',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

