<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBusinessMessagesService
{
    protected string $baseUrl;
    protected string $serviceAccount;

    public function __construct()
    {
        $this->baseUrl = config('services.gmb.base_url', 'https://businessmessages.googleapis.com/v1');
        $this->serviceAccount = config('services.gmb.service_account_key');
    }

    /**
     * Send text message.
     */
    public function sendTextMessage(Conversation $conversation, Message $message): array
    {
        $conversationId = $conversation->platform_conversation_id;

        $payload = [
            'messageId' => uniqid('msg_'),
            'representative' => [
                'representativeType' => 'BOT',
            ],
            'text' => $message->content,
        ];

        return $this->sendRequest($conversationId, $payload, $message);
    }

    /**
     * Send suggestion chips (quick replies).
     */
    public function sendSuggestions(Conversation $conversation, Message $message, array $suggestions): array
    {
        $conversationId = $conversation->platform_conversation_id;

        $formattedSuggestions = [];
        foreach ($suggestions as $suggestion) {
            $formattedSuggestions[] = [
                'reply' => [
                    'text' => $suggestion['text'],
                    'postbackData' => $suggestion['data'] ?? $suggestion['text'],
                ],
            ];
        }

        $payload = [
            'messageId' => uniqid('msg_'),
            'representative' => [
                'representativeType' => 'BOT',
            ],
            'text' => $message->content,
            'suggestions' => $formattedSuggestions,
        ];

        return $this->sendRequest($conversationId, $payload, $message);
    }

    protected function sendRequest(string $conversationId, array $payload, Message $message): array
    {
        try {
            $url = "{$this->baseUrl}/conversations/{$conversationId}/messages";

            // Get OAuth token
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $message->markAsSent($payload['messageId']);
                return ['success' => true, 'platform_message_id' => $payload['messageId']];
            }

            $error = $response->json();
            $message->markAsFailed($error['error']['message'] ?? 'Unknown error');

            return ['success' => false, 'error' => $error];

        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get OAuth access token for Google API.
     */
    protected function getAccessToken(): string
    {
        // This would use Google Service Account credentials
        // For simplicity, returning from config
        return config('services.gmb.access_token');
    }
}

