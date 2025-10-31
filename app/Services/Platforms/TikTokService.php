<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService
{
    protected string $baseUrl;
    protected string $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.tiktok.base_url', 'https://business-api.tiktok.com');
        $this->accessToken = config('services.tiktok.access_token');
    }

    /**
     * Send text message.
     */
    public function sendTextMessage(Conversation $conversation, Message $message): array
    {
        $recipientId = $this->extractRecipientId($conversation);

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'text' => $message->content,
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send image message.
     */
    public function sendImageMessage(Conversation $conversation, Message $message, string $imageUrl): array
    {
        $recipientId = $this->extractRecipientId($conversation);

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'attachment' => [
                    'type' => 'image',
                    'payload' => [
                        'url' => $imageUrl,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send request to TikTok API.
     */
    protected function sendRequest(array $payload, Message $message): array
    {
        try {
            $url = "{$this->baseUrl}/v1.0/messages/send";

            $response = Http::withToken($this->accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $platformMessageId = $data['message_id'] ?? null;

                $message->markAsSent($platformMessageId);

                return [
                    'success' => true,
                    'platform_message_id' => $platformMessageId,
                ];
            }

            $error = $response->json();
            $message->markAsFailed($error['message'] ?? 'Unknown error');

            return ['success' => false, 'error' => $error];

        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function extractRecipientId(Conversation $conversation): string
    {
        return $conversation->platform_conversation_id 
            ?? throw new \Exception('No TikTok recipient ID found');
    }
}
