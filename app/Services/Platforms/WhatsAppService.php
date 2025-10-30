<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $apiVersion;
    protected string $phoneNumberId;
    protected string $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.base_url');
        $this->apiVersion = config('services.whatsapp.api_version');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken = config('services.whatsapp.access_token');
    }

    /**
     * Send a text message.
     */
    public function sendTextMessage(Conversation $conversation, Message $message): array
    {
        $to = $this->extractPhoneNumber($conversation);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message->content,
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send a template message.
     */
    public function sendTemplateMessage(
        Conversation $conversation,
        Message $message,
        string $templateName,
        array $parameters = [],
        string $languageCode = 'en'
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        // Build components for template parameters
        $components = [];
        
        if (!empty($parameters)) {
            $bodyParameters = [];
            foreach ($parameters as $param) {
                $bodyParameters[] = [
                    'type' => 'text',
                    'text' => $param,
                ];
            }
            
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParameters,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
                'components' => $components,
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
        string $imageUrl,
        string $caption = null
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl,
            ],
        ];

        if ($caption) {
            $payload['image']['caption'] = $caption;
        }

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send a video message.
     */
    public function sendVideoMessage(
        Conversation $conversation,
        Message $message,
        string $videoUrl,
        string $caption = null
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'video',
            'video' => [
                'link' => $videoUrl,
            ],
        ];

        if ($caption) {
            $payload['video']['caption'] = $caption;
        }

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send a document message.
     */
    public function sendDocumentMessage(
        Conversation $conversation,
        Message $message,
        string $documentUrl,
        string $filename = null,
        string $caption = null
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $documentUrl,
            ],
        ];

        if ($filename) {
            $payload['document']['filename'] = $filename;
        }

        if ($caption) {
            $payload['document']['caption'] = $caption;
        }

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
        $to = $this->extractPhoneNumber($conversation);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'audio',
            'audio' => [
                'link' => $audioUrl,
            ],
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send interactive button message.
     */
    public function sendInteractiveButtons(
        Conversation $conversation,
        Message $message,
        string $bodyText,
        array $buttons,
        string $headerText = null,
        string $footerText = null
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        // Format buttons (max 3)
        $formattedButtons = [];
        foreach (array_slice($buttons, 0, 3) as $index => $button) {
            $formattedButtons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => $button['id'] ?? "btn_{$index}",
                    'title' => substr($button['title'], 0, 20), // Max 20 chars
                ],
            ];
        }

        $interactive = [
            'type' => 'button',
            'body' => [
                'text' => $bodyText,
            ],
            'action' => [
                'buttons' => $formattedButtons,
            ],
        ];

        if ($headerText) {
            $interactive['header'] = [
                'type' => 'text',
                'text' => $headerText,
            ];
        }

        if ($footerText) {
            $interactive['footer'] = [
                'text' => $footerText,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send interactive list message.
     */
    public function sendInteractiveList(
        Conversation $conversation,
        Message $message,
        string $bodyText,
        string $buttonText,
        array $sections,
        string $headerText = null,
        string $footerText = null
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        $interactive = [
            'type' => 'list',
            'body' => [
                'text' => $bodyText,
            ],
            'action' => [
                'button' => $buttonText,
                'sections' => $sections,
            ],
        ];

        if ($headerText) {
            $interactive['header'] = [
                'type' => 'text',
                'text' => $headerText,
            ];
        }

        if ($footerText) {
            $interactive['footer'] = [
                'text' => $footerText,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Send location message.
     */
    public function sendLocationMessage(
        Conversation $conversation,
        Message $message,
        float $latitude,
        float $longitude,
        string $name = null,
        string $address = null
    ): array {
        $to = $this->extractPhoneNumber($conversation);

        $location = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($name) {
            $location['name'] = $name;
        }

        if ($address) {
            $location['address'] = $address;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'location',
            'location' => $location,
        ];

        return $this->sendRequest($payload, $message);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(string $messageId): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $response = Http::withToken($this->accessToken)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => $messageId,
            ]);

        if ($response->successful()) {
            return ['success' => true];
        }

        Log::error('Failed to mark WhatsApp message as read', [
            'message_id' => $messageId,
            'response' => $response->json(),
        ]);

        return ['success' => false, 'error' => $response->json()];
    }

    /**
     * Send request to WhatsApp API.
     */
    protected function sendRequest(array $payload, Message $message): array
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

            Log::info('Sending WhatsApp message', [
                'payload' => $payload,
                'message_id' => $message->id,
            ]);

            $response = Http::withToken($this->accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $platformMessageId = $data['messages'][0]['id'] ?? null;

                // Update message with platform ID and mark as sent
                $message->markAsSent($platformMessageId);

                Log::info('WhatsApp message sent successfully', [
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

            Log::error('WhatsApp message send failed', [
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

            Log::error('Exception sending WhatsApp message', [
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
     * Extract phone number from conversation.
     */
    protected function extractPhoneNumber(Conversation $conversation): string
    {
        // Try platform_conversation_id first (stored as phone number)
        if ($conversation->platform_conversation_id) {
            return $this->cleanPhoneNumber($conversation->platform_conversation_id);
        }

        // Fallback to customer phone
        if ($conversation->customer->phone) {
            return $this->cleanPhoneNumber($conversation->customer->phone);
        }

        throw new \Exception('No phone number found for conversation');
    }

    /**
     * Clean phone number to E.164 format.
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure it starts with +
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Get media URL from WhatsApp media ID.
     */
    public function getMediaUrl(string $mediaId): ?string
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiVersion}/{$mediaId}";

            $response = Http::withToken($this->accessToken)->get($url);

            if ($response->successful()) {
                return $response->json('url');
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting WhatsApp media URL', ['media_id' => $mediaId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Download media from WhatsApp.
     */
    public function downloadMedia(string $mediaUrl): ?string
    {
        try {
            $response = Http::withToken($this->accessToken)->get($mediaUrl);

            if ($response->successful()) {
                return $response->body();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error downloading WhatsApp media', ['url' => $mediaUrl, 'error' => $e->getMessage()]);
            return null;
        }
    }
}

