<?php

namespace App\Services\Tenant\Actions\Conversation\Platforms;

use App\DTOs\Abstract\BaseDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;

interface PlatformInterface
{
    public function sendMessage(SendMessageDTO $messageDTO): mixed;
    public function receiveMessage(BaseDTO $dto): mixed;

    public function getPlatformName(): string;
}
