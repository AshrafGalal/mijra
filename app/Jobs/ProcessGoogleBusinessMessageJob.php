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

class ProcessGoogleBusinessMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload
    ) {
        //
    }

    public function handle(ConversationService $conversationService, MessageService $messageService): void
    {
        try {
            $message = $this->payload['message'] ?? [];
            $conversationId = $this->payload['conversationId'] ?? null;
            $messageId = $message['messageId'] ?? null;
            $text = $message['text'] ?? '';
            $senderId = $message['name'] ?? $conversationId;

            if (!$senderId || !$messageId) {
                return;
            }

            // Find or create customer
            $customer = $this->findOrCreateCustomer($senderId);

            // Find or create conversation
            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::GMB->value,
                platformConversationId: $conversationId
            );

            // Create inbound message
            $inboundMessage = $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: $text,
                type: MessageTypeEnum::TEXT->value,
                platformMessageId: $messageId,
                metadata: [
                    'conversation_id' => $conversationId,
                    'sender' => $message['name'] ?? null,
                ]
            );

            // Auto-assign and automated replies
            if (!$conversation->assigned_to) {
                $autoAssignmentService = app(\App\Services\Tenant\AutoAssignmentService::class);
                if ($autoAssignmentService->isEnabled()) {
                    $autoAssignmentService->autoAssign($conversation->fresh());
                }
            }

            $automatedReplyService = app(\App\Services\Tenant\AutomatedReplyService::class);
            $automatedReplyService->processMessage($inboundMessage, $conversation->fresh());

            Log::info('Google Business Message processed', [
                'message_id' => $messageId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Google Business Message', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function findOrCreateCustomer(string $senderId): Customer
    {
        $customer = Customer::whereHas('socialAccounts', function ($query) use ($senderId) {
            $query->where('platform_account_id', $senderId)
                ->where('provider_name', 'gmb');
        })->first();

        if (!$customer) {
            $customer = Customer::create([
                'name' => "GMB User {$senderId}",
                'source' => \App\Enum\CustomerSourceEnum::SHOPIFY->value,
                'status' => \App\Enum\CustomerStatusEnum::LEAD->value,
            ]);

            $customer->socialAccounts()->create([
                'provider_name' => 'gmb',
                'platform_account_id' => $senderId,
                'account_name' => "GMB User",
                'username' => $senderId,
            ]);
        }

        return $customer;
    }
}

