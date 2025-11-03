<?php

namespace App\Services\Landlord\Channels;

use App\DTOs\Abstract\BaseDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;
use App\Enum\ExternalPlatformEnum;
use App\Services\Landlord\Facebook\FacebookPlatform;
use App\Services\Landlord\Facebook\Http;
use App\Services\Tenant\Actions\Conversation\Platforms\PlatformInterface;

class MessengerService implements PlatformInterface
{
    public function sendMessage(SendMessageDTO $sendMessageDTO): array
    {

    }

    public function getPlatformName(): string
    {
        return ExternalPlatformEnum::MESSENGER->value;
    }

    public function receiveMessage(BaseDTO $dto): mixed
    {
        logger()->info('Received message from messenger');
        logger()->info(json_encode($dto->toArray()));
    }
}
