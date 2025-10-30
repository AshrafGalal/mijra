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

class ProcessFacebookMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $event
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ConversationService $conversationService, MessageService $messageService): void
    {
        try {
            $sender = $this->event['sender']['id'] ?? null;
            $recipient = $this->event['recipient']['id'] ?? null;
            $message = $this->event['message'] ?? [];
            $messageId = $message['mid'] ?? null;
            $timestamp = $this->event['timestamp'] ?? null;

            if (!$sender || !$messageId) {
                Log::error('Facebook message missing required fields', ['event' => $this->event]);
                return;
            }

            // Find or create customer by Facebook sender ID
            $customer = $this->findOrCreateCustomer($sender);

            // Find or create conversation
            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::FACEBOOK->value,
                platformConversationId: $sender // Use sender ID as conversation ID
            );

            // Extract message content
            $text = $message['text'] ?? '';
            $attachments = $this->extractAttachments($message);
            $messageType = $this->determineMessageType($message);

            // Create inbound message
            $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: $text ?: $this->getDefaultContent($messageType),
                type: $messageType,
                platformMessageId: $messageId,
                metadata: [
                    'timestamp' => $timestamp,
                    'sender_id' => $sender,
                    'recipient_id' => $recipient,
                    'quick_reply' => $message['quick_reply'] ?? null,
                ],
                attachments: $attachments
            );

            Log::info('Facebook message processed successfully', [
                'message_id' => $messageId,
                'conversation_id' => $conversation->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Facebook message', [
                'error' => $e->getMessage(),
                'event' => $this->event,
            ]);
            throw $e;
        }
    }

    /**
     * Find or create customer by Facebook sender ID.
     */
    protected function findOrCreateCustomer(string $senderId): Customer
    {
        // Try to find customer by Facebook platform account
        $customer = Customer::whereHas('socialAccounts', function ($query) use ($senderId) {
            $query->where('platform_account_id', $senderId)
                ->where('provider_name', 'facebook');
        })->first();

        if (!$customer) {
            // Fetch user info from Facebook Graph API
            $userInfo = $this->fetchFacebookUserInfo($senderId);

            $customer = Customer::create([
                'name' => $userInfo['name'] ?? "Facebook User {$senderId}",
                'source' => \App\Enum\CustomerSourceEnum::FACEBOOK->value,
                'status' => \App\Enum\CustomerStatusEnum::LEAD->value,
            ]);

            // Link social account
            $customer->socialAccounts()->create([
                'provider_name' => 'facebook',
                'platform_account_id' => $senderId,
                'account_name' => $userInfo['name'] ?? '',
                'username' => $userInfo['username'] ?? '',
                'metadata' => $userInfo,
            ]);

            Log::info('New customer created from Facebook', ['customer_id' => $customer->id]);
        }

        return $customer;
    }

    /**
     * Fetch user info from Facebook Graph API.
     */
    protected function fetchFacebookUserInfo(string $senderId): array
    {
        try {
            $accessToken = config('services.facebook.page_access_token');
            
            if (!$accessToken) {
                return [];
            }

            $response = Http::get("https://graph.facebook.com/v23.0/{$senderId}", [
                'fields' => 'id,name,first_name,last_name,profile_pic',
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (\Exception $e) {
            Log::warning('Could not fetch Facebook user info', ['sender_id' => $senderId]);
            return [];
        }
    }

    /**
     * Extract attachments from message.
     */
    protected function extractAttachments(array $message): array
    {
        $attachments = [];
        $messageAttachments = $message['attachments'] ?? [];

        foreach ($messageAttachments as $attachment) {
            $type = $attachment['type'] ?? 'file';
            $payload = $attachment['payload'] ?? [];
            $url = $payload['url'] ?? null;

            if ($url) {
                $attachments[] = [
                    'type' => $this->mapFacebookAttachmentType($type),
                    'url' => $url,
                    'platform_url' => $url,
                    'mime_type' => null,
                    'filename' => $payload['title'] ?? null,
                ];
            }
        }

        return $attachments;
    }

    /**
     * Determine message type.
     */
    protected function determineMessageType(array $message): string
    {
        if (isset($message['attachments'])) {
            $type = $message['attachments'][0]['type'] ?? 'file';
            return $this->mapFacebookAttachmentType($type);
        }

        if (isset($message['text'])) {
            return MessageTypeEnum::TEXT->value;
        }

        return MessageTypeEnum::TEXT->value;
    }

    /**
     * Map Facebook attachment type to internal type.
     */
    protected function mapFacebookAttachmentType(string $fbType): string
    {
        return match ($fbType) {
            'image' => MessageTypeEnum::IMAGE->value,
            'video' => MessageTypeEnum::VIDEO->value,
            'audio' => MessageTypeEnum::AUDIO->value,
            'file' => MessageTypeEnum::DOCUMENT->value,
            default => MessageTypeEnum::DOCUMENT->value,
        };
    }

    /**
     * Get default content for non-text messages.
     */
    protected function getDefaultContent(string $type): string
    {
        return match ($type) {
            MessageTypeEnum::IMAGE->value => 'Image',
            MessageTypeEnum::VIDEO->value => 'Video',
            MessageTypeEnum::AUDIO->value => 'Audio',
            MessageTypeEnum::DOCUMENT->value => 'Document',
            default => 'Message',
        };
    }
}

