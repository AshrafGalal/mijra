<?php

namespace App\Enum;

enum ConversationTypeEnum: int
{
    case INDIVIDUAL = 1;
    case GROUP = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::INDIVIDUAL => __('app.individual'),
            self::GROUP => __('app.group'),
        };
    }

    public function typeText(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'individual',
            self::GROUP => 'group',
        };
    }
}
