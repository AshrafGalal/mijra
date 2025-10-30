<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookMessengerService
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
     * Send a file/document message.
     */
    public function sendFileMessage(
        Conversation $conversation,
        Message $message,
        string $fileUrl
    ): array {
        $recipientId = $this->extractRecipientId($conversation);

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'attachment' => [
                    'type' => 'file',
                    'payload' => [
                        'url' => $fileUrl,
                        'is_reusable' => true,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send an audio message.
     */
    public function sendAudioMessage(
        Conversation $conversation,
        Message $message,
        string $audioUrl
    ): array {
        $recipientId = $this->extractRecipientId($conversation);

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'attachment' => [
                    'type' => 'audio',
                    'payload' => [
                        'url' => $audioUrl,
                        'is_reusable' => true,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send quick replies.
     */
    public function sendQuickReplies(
        Conversation $conversation,
        Message $message,
        string $text,
        array $quickReplies
    ): array {
        $recipientId = $this->extractRecipientId($conversation);

        // Format quick replies (max 13)
        $formattedReplies = [];
        foreach (array_slice($quickReplies, 0, 13) as $reply) {
            $formattedReplies[] = [
                'content_type' => 'text',
                'title' => substr($reply['title'], 0, 20), // Max 20 chars
                'payload' => $reply['payload'] ?? $reply['title'],
            ];
        }

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'text' => $text,
                'quick_replies' => $formattedReplies,
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send button template.
     */
    public function sendButtonTemplate(
        Conversation $conversation,
        Message $message,
        string $text,
        array $buttons
    ): array {
        $recipientId = $this->extractRecipientId($conversation);

        // Format buttons (max 3)
        $formattedButtons = [];
        foreach (array_slice($buttons, 0, 3) as $button) {
            $formattedButtons[] = [
                'type' => 'postback',
                'title' => substr($button['title'], 0, 20),
                'payload' => $button['payload'] ?? $button['title'],
            ];
        }

        $payload = [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text' => substr($text, 0, 640), // Max 640 chars
                        'buttons' => $formattedButtons,
                    ],
                ],
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send generic template (carousel).
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
     * Send typing indicator.
     */
    public function sendTypingOn(string $recipientId): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/me/messages";

        $response = Http::withQueryParameters(['access_token' => $this->pageAccessToken])
            ->post($url, [
                'recipient' => ['id' => $recipientId],
                'sender_action' => 'typing_on',
            ]);

        return ['success' => $response->successful()];
    }

    /**
     * Send request to Facebook Messenger API.
     */
    protected function sendRequest(array $payload, Message $message): array
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiVersion}/me/messages";

            Log::info('Sending Facebook Messenger message', [
                'payload' => $payload,
                'message_id' => $message->id,
            ]);

            $response = Http::withQueryParameters(['access_token' => $this->pageAccessToken])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $platformMessageId = $data['message_id'] ?? null;

                // Update message with platform ID and mark as sent
                $message->markAsSent($platformMessageId);

                Log::info('Facebook message sent successfully', [
                    'message_id' => $message->id,
                    'platform_message_id' => $platformMessageId,
                ]);

                return [
                    'success' => true,
                    'platform_message_id' => $platformMessageId,
                    'data' => $data,
                ];
            }

            // Handle failure
            $error = $response->json();
            $errorMessage = $error['error']['message'] ?? 'Unknown error';

            $message->markAsFailed($errorMessage);

            Log::error('Facebook message send failed', [
                'message_id' => $message->id,
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error,
                'message' => $errorMessage,
            ];

        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());

            Log::error('Exception sending Facebook message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extract recipient ID from conversation.
     */
    protected function extractRecipientId(Conversation $conversation): string
    {
        // Use platform_conversation_id (Facebook sender ID)
        if ($conversation->platform_conversation_id) {
            return $conversation->platform_conversation_id;
        }

        throw new \Exception('No Facebook recipient ID found for conversation');
    }
}

