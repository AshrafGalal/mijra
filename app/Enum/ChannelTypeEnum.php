<?php

namespace App\Enum;

enum ChannelTypeEnum: string
{
    case FACEBOOK_PAGE = 'facebook_page';
    case INSTAGRAM_ACCOUNT = 'instagram_account';
    case SHOPIFY_STORE = 'shopify_store';
    case TIKTOK_ACCOUNT = 'tiktok_account';
    case WHATSAPP_ACCOUNT = 'whatsapp_account';

    public function label(): string
    {
        return match($this) {
            self::FACEBOOK_PAGE => 'Facebook Page',
            self::INSTAGRAM_ACCOUNT => 'Instagram Account',
            self::SHOPIFY_STORE => 'Shopify Store',
            self::TIKTOK_ACCOUNT => 'TikTok Account',
            self::WHATSAPP_ACCOUNT => 'WhatsApp Business',
        };
    }

    public function platform(): string
    {
        return match($this) {
            self::FACEBOOK_PAGE => ExternalPlatformEnum::MESSENGER->value,
            self::INSTAGRAM_ACCOUNT => ExternalPlatformEnum::INSTAGRAM->value,
            self::SHOPIFY_STORE => ExternalPlatformEnum::SHOPIFY->value,
            self::TIKTOK_ACCOUNT => ExternalPlatformEnum::TIKTOK->value,
            self::WHATSAPP_ACCOUNT => ExternalPlatformEnum::WHATSAPP->value,
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::FACEBOOK_PAGE => 'facebook',
            self::INSTAGRAM_ACCOUNT => 'instagram',
            self::SHOPIFY_STORE => 'shopping-cart',
            self::TIKTOK_ACCOUNT => 'tiktok',
            self::WHATSAPP_ACCOUNT => 'whatsapp',
        };
    }

    public function capabilities(): array
    {
        return match($this) {
            self::FACEBOOK_PAGE => ['messaging', 'posts', 'comments', 'ads'],
            self::INSTAGRAM_ACCOUNT => ['messaging', 'posts', 'comments', 'insights'],
            self::SHOPIFY_STORE => ['products', 'orders', 'customers', 'inventory'],
            self::TIKTOK_ACCOUNT => ['posts', 'comments', 'analytics'],
            self::WHATSAPP_ACCOUNT => ['messaging', 'templates', 'contacts'],
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
            array_map(fn($type) => $type->label(), self::cases())
        );
    }

    public static function forPlatform(string $platform): array
    {
        return array_filter(
            self::cases(),
            fn($type) => $type->platform() === $platform
        );
    }
}
