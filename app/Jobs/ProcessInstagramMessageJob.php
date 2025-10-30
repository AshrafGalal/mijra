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

class ProcessInstagramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $event
    ) {
        //
    }

    public function handle(ConversationService $conversationService, MessageService $messageService): void
    {
        try {
            $sender = $this->event['sender']['id'] ?? null;
            $message = $this->event['message'] ?? [];
            $messageId = $message['mid'] ?? null;

            if (!$sender || !$messageId) {
                Log::error('Instagram message missing required fields', ['event' => $this->event]);
                return;
            }

            // Find or create customer
            $customer = $this->findOrCreateCustomer($sender);

            // Find or create conversation
            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::INSTAGRAM->value,
                platformConversationId: $sender
            );

            // Extract message content
            $text = $message['text'] ?? '';
            $attachments = $this->extractAttachments($message);
            $messageType = $this->determineMessageType($message);

            // Check if this is a story reply or mention
            $isStoryReply = isset($message['is_echo']) === false && isset($message['reply_to']);
            
            // Create inbound message
            $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: $text ?: $this->getDefaultContent($messageType),
                type: $messageType,
                platformMessageId: $messageId,
                metadata: [
                    'sender_id' => $sender,
                    'is_story_reply' => $isStoryReply,
                    'reply_to' => $message['reply_to'] ?? null,
                    'timestamp' => $this->event['timestamp'] ?? null,
                ],
                attachments: $attachments
            );

            Log::info('Instagram message processed successfully', [
                'message_id' => $messageId,
                'conversation_id' => $conversation->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Instagram message', [
                'error' => $e->getMessage(),
                'event' => $this->event,
            ]);
            throw $e;
        }
    }

    protected function findOrCreateCustomer(string $senderId): Customer
    {
        $customer = Customer::whereHas('socialAccounts', function ($query) use ($senderId) {
            $query->where('platform_account_id', $senderId)
                ->where('provider_name', 'instagram');
        })->first();

        if (!$customer) {
            $userInfo = $this->fetchInstagramUserInfo($senderId);

            $customer = Customer::create([
                'name' => $userInfo['name'] ?? $userInfo['username'] ?? "Instagram User {$senderId}",
                'source' => \App\Enum\CustomerSourceEnum::INSTAGRAM->value,
                'status' => \App\Enum\CustomerStatusEnum::LEAD->value,
            ]);

            $customer->socialAccounts()->create([
                'provider_name' => 'instagram',
                'platform_account_id' => $senderId,
                'account_name' => $userInfo['name'] ?? '',
                'username' => $userInfo['username'] ?? '',
                'metadata' => $userInfo,
            ]);
        }

        return $customer;
    }

    protected function fetchInstagramUserInfo(string $senderId): array
    {
        try {
            $accessToken = config('services.facebook.page_access_token');
            
            if (!$accessToken) {
                return [];
            }

            $response = Http::get("https://graph.facebook.com/v23.0/{$senderId}", [
                'fields' => 'id,name,username,profile_pic',
                'access_token' => $accessToken,
            ]);

            return $response->successful() ? $response->json() : [];

        } catch (\Exception $e) {
            return [];
        }
    }

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
                    'type' => $this->mapInstagramAttachmentType($type),
                    'url' => $url,
                    'platform_url' => $url,
                ];
            }
        }

        return $attachments;
    }

    protected function determineMessageType(array $message): string
    {
        if (isset($message['attachments'])) {
            $type = $message['attachments'][0]['type'] ?? 'file';
            return $this->mapInstagramAttachmentType($type);
        }

        return MessageTypeEnum::TEXT->value;
    }

    protected function mapInstagramAttachmentType(string $type): string
    {
        return match ($type) {
            'image' => MessageTypeEnum::IMAGE->value,
            'video' => MessageTypeEnum::VIDEO->value,
            'audio' => MessageTypeEnum::AUDIO->value,
            'file' => MessageTypeEnum::DOCUMENT->value,
            'story_mention' => MessageTypeEnum::TEXT->value,
            default => MessageTypeEnum::DOCUMENT->value,
        };
    }

    protected function getDefaultContent(string $type): string
    {
        return match ($type) {
            MessageTypeEnum::IMAGE->value => 'Image',
            MessageTypeEnum::VIDEO->value => 'Video',
            MessageTypeEnum::AUDIO->value => 'Audio',
            default => 'Message',
        };
    }
}

