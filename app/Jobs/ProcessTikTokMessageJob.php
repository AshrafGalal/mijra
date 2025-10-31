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
use Illuminate\Support\Facades\Log;

class ProcessTikTokMessageJob implements ShouldQueue
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
            $message = $this->event['message'] ?? [];
            $senderId = $message['sender_id'] ?? null;
            $messageId = $message['message_id'] ?? null;
            $content = $message['text'] ?? '';

            if (!$senderId || !$messageId) {
                return;
            }

            // Find or create customer
            $customer = $this->findOrCreateCustomer($senderId);

            // Find or create conversation
            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::TIKTOK->value,
                platformConversationId: $senderId
            );

            // Create inbound message
            $inboundMessage = $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: $content,
                type: MessageTypeEnum::TEXT->value,
                platformMessageId: $messageId,
                metadata: [
                    'sender_id' => $senderId,
                    'timestamp' => $this->event['timestamp'] ?? null,
                ]
            );

            // Auto-assign and check automated replies
            if (!$conversation->assigned_to) {
                $autoAssignmentService = app(\App\Services\Tenant\AutoAssignmentService::class);
                if ($autoAssignmentService->isEnabled()) {
                    $autoAssignmentService->autoAssign($conversation->fresh());
                }
            }

            $automatedReplyService = app(\App\Services\Tenant\AutomatedReplyService::class);
            $automatedReplyService->processMessage($inboundMessage, $conversation->fresh());

            Log::info('TikTok message processed successfully', [
                'message_id' => $messageId,
                'conversation_id' => $conversation->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing TikTok message', [
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
                ->where('provider_name', 'tiktok');
        })->first();

        if (!$customer) {
            $customer = Customer::create([
                'name' => "TikTok User {$senderId}",
                'source' => \App\Enum\CustomerSourceEnum::WHATSAPP->value, // Will add TIKTOK enum
                'status' => \App\Enum\CustomerStatusEnum::LEAD->value,
            ]);

            $customer->socialAccounts()->create([
                'provider_name' => 'tiktok',
                'platform_account_id' => $senderId,
                'account_name' => "TikTok User {$senderId}",
                'username' => $senderId,
            ]);
        }

        return $customer;
    }
}


