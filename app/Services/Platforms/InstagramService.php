<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    protected string $baseUrl;
    protected string $apiVersion;
    protected string $pageAccessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.facebook.base_url', 'https://graph.facebook.com');
        $this->apiVersion = 'v23.0';
        $this->pageAccessToken = config('services.facebook.page_access_token');
    }

    /**
     * Send a text message.
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
     * Send an image message.
     */
    public function sendImageMessage(
        Conversation $conversation,
        Message $message,
        string $imageUrl
    ): array {
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
                        'is_reusable' => true,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send a video message.
     */
    public function sendVideoMessage(
        Conversation $conversation,
        Message $message,
        string $videoUrl
    ): array {
        $recipientId = $this->extractRecipientId($conversation);

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'attachment' => [
                    'type' => 'video',
                    'payload' => [
                        'url' => $videoUrl,
                        'is_reusable' => true,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send generic template with buttons.
     */
    public function sendGenericTemplate(
        Conversation $conversation,
        Message $message,
        array $elements
    ): array {
        $recipientId = $this->extractRecipientId($conversation);

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'generic',
                        'elements' => $elements,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send request to Instagram Messaging API.
     */
    protected function sendRequest(array $payload, Message $message): array
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiVersion}/me/messages";

            Log::info('Sending Instagram message', [
                'payload' => $payload,
                'message_id' => $message->id,
            ]);

            $response = Http::withQueryParameters(['access_token' => $this->pageAccessToken])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $platformMessageId = $data['message_id'] ?? null;

                $message->markAsSent($platformMessageId);

                Log::info('Instagram message sent successfully', [
                    'message_id' => $message->id,
                    'platform_message_id' => $platformMessageId,
                ]);

                return [
                    'success' => true,
                    'platform_message_id' => $platformMessageId,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            $errorMessage = $error['error']['message'] ?? 'Unknown error';
            $message->markAsFailed($errorMessage);

            return [
                'success' => false,
                'error' => $error,
                'message' => $errorMessage,
            ];

        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function extractRecipientId(Conversation $conversation): string
    {
        if ($conversation->platform_conversation_id) {
            return $conversation->platform_conversation_id;
        }

        throw new \Exception('No Instagram recipient ID found for conversation');
    }
}

