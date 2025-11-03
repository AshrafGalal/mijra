<?php

namespace App\Services\Landlord\Channels;

use App\DTOs\Abstract\BaseDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;
use App\Enum\ExternalPlatformEnum;
use App\Services\Landlord\Facebook\FacebookPlatform;
use App\Services\Landlord\Facebook\Http;
use App\Services\Tenant\Actions\Conversation\Platforms\PlatformInterface;

class InstagramService implements PlatformInterface
{
    public function sendMessage(SendMessageDTO $sendMessageDTO): array
    {
        $platform = FacebookPlatform::where('page_id', $pageId)->first();

        if (!$platform) {
            return [
                'success' => false,
                'message' => 'Page not found'
            ];
        }

        $response = Http::post("https://graph.facebook.com/v18.0/{$pageId}/messages", [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $message],
            'messaging_type' => 'RESPONSE',
            'access_token' => $platform->page_access_token,
        ]);

        return [
            'success' => $response->successful(),
            'message' => $response->successful() ? 'Message sent successfully' : 'Failed to send message',
            'data' => $response->json()
        ];
    }

    public function getPlatformName(): string
    {
        return ExternalPlatformEnum::MESSENGER->value;
    }

    public function receiveMessage(BaseDTO $dto): mixed
    {
        // TODO: Implement receiveMessage() method.
    }
}
