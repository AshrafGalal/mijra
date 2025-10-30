<?php

namespace App\Enum;

enum ConversationStatusEnum: string
{
    case NEW = 'new';
    case OPEN = 'open';
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::OPEN => 'Open',
            self::PENDING => 'Pending',
            self::RESOLVED => 'Resolved',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => '#3B82F6', // Blue
            self::OPEN => '#10B981', // Green
            self::PENDING => '#F59E0B', // Amber
            self::RESOLVED => '#6B7280', // Gray
            self::ARCHIVED => '#9CA3AF', // Light Gray
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}



