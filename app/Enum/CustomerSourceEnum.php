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
    case TIKTOK = 7;
    case SALLA = 8;
    case WOOCOMMERCE = 9;
    case GMB = 10;
    case EMAIL = 11;
    case SMS = 12;

    public function getLabel(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::WEBSITE => 'Website',
            self::WHATSAPP => 'WhatsApp',
            self::FACEBOOK => 'Facebook',
            self::INSTAGRAM => 'Instagram',
            self::SHOPIFY => 'Shopify',
            self::TIKTOK => 'TikTok',
            self::SALLA => 'Salla',
            self::WOOCOMMERCE => 'WooCommerce',
            self::GMB => 'Google Business',
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
