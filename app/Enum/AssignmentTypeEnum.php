<?php

namespace App\Enum;

enum AssignmentTypeEnum: string
{
    case MANUAL = 'manual';
    case AUTO_ROUND_ROBIN = 'auto_round_robin';
    case AUTO_LOAD_BASED = 'auto_load_based';
    case AUTO_AVAILABILITY = 'auto_availability';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual Assignment',
            self::AUTO_ROUND_ROBIN => 'Auto (Round Robin)',
            self::AUTO_LOAD_BASED => 'Auto (Load Based)',
            self::AUTO_AVAILABILITY => 'Auto (Availability Based)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

