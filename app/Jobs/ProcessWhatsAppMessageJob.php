<?php

namespace App\Jobs;

use App\Enum\ExternalPlatformEnum;
use App\Enum\MessageTypeEnum;
use App\Models\Tenant\Customer;
use App\Services\Tenant\ConversationService;
use App\Services\Tenant\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $message,
        public array $metadata
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ConversationService $conversationService, MessageService $messageService): void
    {
        try {
            // Extract data from WhatsApp webhook payload
            $from = $this->message['from'] ?? null;
            $messageId = $this->message['id'] ?? null;
            $timestamp = $this->message['timestamp'] ?? null;
            $type = $this->message['type'] ?? 'text';

            if (!$from || !$messageId) {
                Log::error('WhatsApp message missing required fields', ['message' => $this->message]);
                return;
            }

            // Find or create customer by phone number
            $customer = $this->findOrCreateCustomer($from);

            // Find or create conversation
            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::WHATSAPP->value,
                platformConversationId: $from // Use phone number as conversation ID
            );

            // Extract message content based on type
            $content = $this->extractMessageContent($type);
            $attachments = $this->extractAttachments($type);

            // Create inbound message
            $message = $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: $content,
                type: $this->mapWhatsAppTypeToMessageType($type),
                platformMessageId: $messageId,
                metadata: [
                    'timestamp' => $timestamp,
                    'from' => $from,
                    'whatsapp_type' => $type,
                ],
                attachments: $attachments
            );

            // Auto-assign conversation if unassigned
            if (!$conversation->assigned_to) {
                $autoAssignmentService = app(\App\Services\Tenant\AutoAssignmentService::class);
                if ($autoAssignmentService->isEnabled()) {
                    $autoAssignmentService->autoAssign($conversation->fresh());
                }
            }

            // Check for automated replies
            $automatedReplyService = app(\App\Services\Tenant\AutomatedReplyService::class);
            $automatedReplyService->processMessage($message, $conversation->fresh());

            Log::info('WhatsApp message processed successfully', [
                'message_id' => $messageId,
                'conversation_id' => $conversation->id,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp message', [
                'error' => $e->getMessage(),
                'message' => $this->message,
            ]);
            throw $e;
        }
    }

    /**
     * Find or create customer by phone number.
     */
    protected function findOrCreateCustomer(string $phoneNumber): Customer
    {
        // Clean phone number (remove "whatsapp:" prefix if present)
        $cleanPhone = str_replace('whatsapp:', '', $phoneNumber);

        $customer = Customer::where('phone', $cleanPhone)->first();

        if (!$customer) {
            // Extract contact info from metadata if available
            $profile = $this->metadata['contacts'][0]['profile'] ?? [];
            
            $customer = Customer::create([
                'name' => $profile['name'] ?? $cleanPhone,
                'phone' => $cleanPhone,
                'source' => \App\Enum\CustomerSourceEnum::WHATSAPP->value,
                'status' => \App\Enum\CustomerStatusEnum::LEAD->value,
            ]);

            Log::info('New customer created from WhatsApp', ['customer_id' => $customer->id, 'phone' => $cleanPhone]);
        }

        return $customer;
    }

    /**
     * Extract message content based on type.
     */
    protected function extractMessageContent(string $type): string
    {
        return match ($type) {
            'text' => $this->message['text']['body'] ?? '',
            'image' => $this->message['image']['caption'] ?? 'Image',
            'video' => $this->message['video']['caption'] ?? 'Video',
            'audio' => 'Audio message',
            'voice' => 'Voice message',
            'document' => $this->message['document']['caption'] ?? $this->message['document']['filename'] ?? 'Document',
            'location' => $this->formatLocation(),
            'contact' => $this->formatContact(),
            'interactive' => $this->extractInteractiveResponse(),
            default => "Unsupported message type: {$type}",
        };
    }

    /**
     * Extract attachments from message.
     */
    protected function extractAttachments(string $type): array
    {
        $attachments = [];

        if (in_array($type, ['image', 'video', 'audio', 'voice', 'document', 'sticker'])) {
            $mediaData = $this->message[$type] ?? [];
            $mediaId = $mediaData['id'] ?? null;

            if ($mediaId) {
                // Download media from WhatsApp
                $mediaUrl = $this->downloadWhatsAppMedia($mediaId);

                if ($mediaUrl) {
                    $attachments[] = [
                        'type' => $type,
                        'url' => $mediaUrl,
                        'platform_url' => $mediaId,
                        'mime_type' => $mediaData['mime_type'] ?? null,
                        'filename' => $mediaData['filename'] ?? null,
                        'file_size' => null, // Will be set during download
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Download media from WhatsApp.
     */
    protected function downloadWhatsAppMedia(string $mediaId): ?string
    {
        try {
            $accessToken = config('services.whatsapp.access_token');
            $apiVersion = config('services.whatsapp.api_version');
            $baseUrl = config('services.whatsapp.base_url');

            // Get media URL from WhatsApp
            $response = Http::withToken($accessToken)
                ->get("{$baseUrl}/{$apiVersion}/{$mediaId}");

            if ($response->failed()) {
                Log::error('Failed to get WhatsApp media URL', ['media_id' => $mediaId]);
                return null;
            }

            $mediaUrl = $response->json('url');

            if (!$mediaUrl) {
                return null;
            }

            // Download the actual media file
            $mediaResponse = Http::withToken($accessToken)->get($mediaUrl);

            if ($mediaResponse->failed()) {
                return null;
            }

            // Store file locally
            $filename = 'whatsapp/' . date('Y-m-d') . '/' . $mediaId . '.' . $this->guessExtension($response->json('mime_type'));
            Storage::disk('public')->put($filename, $mediaResponse->body());

            return Storage::disk('public')->url($filename);

        } catch (\Exception $e) {
            Log::error('Error downloading WhatsApp media', ['error' => $e->getMessage(), 'media_id' => $mediaId]);
            return null;
        }
    }

    /**
     * Guess file extension from mime type.
     */
    protected function guessExtension(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'application/pdf' => 'pdf',
            default => 'bin',
        };
    }

    /**
     * Format location message.
     */
    protected function formatLocation(): string
    {
        $location = $this->message['location'] ?? [];
        $lat = $location['latitude'] ?? '';
        $lng = $location['longitude'] ?? '';
        $name = $location['name'] ?? '';
        $address = $location['address'] ?? '';

        return trim("{$name}\n{$address}\nLocation: {$lat}, {$lng}");
    }

    /**
     * Format contact message.
     */
    protected function formatContact(): string
    {
        $contacts = $this->message['contacts'] ?? [];
        $formatted = [];

        foreach ($contacts as $contact) {
            $name = $contact['name']['formatted_name'] ?? 'Unknown';
            $phones = collect($contact['phones'] ?? [])->pluck('phone')->join(', ');
            $formatted[] = "{$name}: {$phones}";
        }

        return 'Contact(s): ' . implode('; ', $formatted);
    }

    /**
     * Extract interactive button/list response.
     */
    protected function extractInteractiveResponse(): string
    {
        $interactive = $this->message['interactive'] ?? [];
        $type = $interactive['type'] ?? '';

        if ($type === 'button_reply') {
            return $interactive['button_reply']['title'] ?? 'Button clicked';
        }

        if ($type === 'list_reply') {
            return $interactive['list_reply']['title'] ?? 'List option selected';
        }

        return 'Interactive response';
    }

    /**
     * Map WhatsApp message type to internal MessageTypeEnum.
     */
    protected function mapWhatsAppTypeToMessageType(string $whatsappType): string
    {
        return match ($whatsappType) {
            'text' => MessageTypeEnum::TEXT->value,
            'image' => MessageTypeEnum::IMAGE->value,
            'video' => MessageTypeEnum::VIDEO->value,
            'audio' => MessageTypeEnum::AUDIO->value,
            'voice' => MessageTypeEnum::VOICE->value,
            'document' => MessageTypeEnum::DOCUMENT->value,
            'location' => MessageTypeEnum::LOCATION->value,
            'contacts' => MessageTypeEnum::CONTACT->value,
            'sticker' => MessageTypeEnum::STICKER->value,
            default => MessageTypeEnum::TEXT->value,
        };
    }
}

