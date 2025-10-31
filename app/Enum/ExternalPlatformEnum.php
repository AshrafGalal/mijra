<?php

namespace App\Enum;

enum ExternalPlatformEnum: string
{
    case FACEBOOK = 'facebook';
    case WHATSAPP = 'whatsapp';
    case INSTAGRAM = 'instagram';
    case TIKTOK = 'tiktok';
    case SHOPIFY = 'shopify';
    case WOOCOMMERCE = 'woocommerce';
    case SALLA = 'salla';
    case ZID = 'zid';
    case PYMOB = 'pymob';
    case MOYASAR = 'moyasar';
    case GMB = 'gmb'; // Google Business Messages
    case EMAIL = 'email';
    case SMS = 'sms';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::FACEBOOK => 'Facebook Messenger',
            self::WHATSAPP => 'WhatsApp',
            self::INSTAGRAM => 'Instagram',
            self::TIKTOK => 'TikTok',
            self::SHOPIFY => 'Shopify',
            self::WOOCOMMERCE => 'WooCommerce',
            self::SALLA => 'Salla',
            self::ZID => 'Zid',
            self::PYMOB => 'Pymob',
            self::MOYASAR => 'Moyasar',
            self::GMB => 'Google Business Messages',
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
        };
    }

    public function isMessaging(): bool
    {
        return in_array($this, [
            self::FACEBOOK,
            self::WHATSAPP,
            self::INSTAGRAM,
            self::TIKTOK,
            self::GMB,
            self::EMAIL,
            self::SMS,
        ]);
    }

    public function isEcommerce(): bool
    {
        return in_array($this, [
            self::SHOPIFY,
            self::WOOCOMMERCE,
            self::SALLA,
            self::ZID,
        ]);
    }

    public function isPayment(): bool
    {
        return in_array($this, [
            self::PYMOB,
            self::MOYASAR,
        ]);
    }
}
