<?php

namespace App\Services\Tenant\Actions\Conversation\Platforms;

use App\Enum\ExternalPlatformEnum;
use App\Tenant\Bridges\Landlord\InstagramChannelBridge;
use App\Tenant\Bridges\Landlord\MessengerChannelBridge;
use InvalidArgumentException;

class PlatformFactory
{
    protected array $map = [
        ExternalPlatformEnum::WHATSAPP->value => WhatsappService::class,
        ExternalPlatformEnum::MESSENGER->value => MessengerChannelBridge::class,
        ExternalPlatformEnum::INSTAGRAM->value => InstagramChannelBridge::class,
    ];

    public function make(string $platform): PlatformInterface
    {
        $platform = strtolower($platform);

        if (!isset($this->map[$platform])) {
            throw new InvalidArgumentException("Unsupported platform: {$platform}");
        }

        return app($this->map[$platform]); // resolve via IoC container
    }
}
