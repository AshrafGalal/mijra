<?php

namespace App\Enum;

enum CustomerSourceEnum: int
{
    case MANUAL = 1;
    case WEBSITE = 2;
    case WHATSAPP = 3;
    case FACEBOOK = 4;
    case INSTAGRAM = 5;
    case SHOPIFY = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::WEBSITE => 'Website',
            self::WHATSAPP => 'WhatsApp',
            self::FACEBOOK => 'Facebook',
            self::INSTAGRAM => 'Instagram',
            self::SHOPIFY => 'Shopify',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
