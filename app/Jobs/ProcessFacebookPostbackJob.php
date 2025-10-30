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

class ProcessFacebookPostbackJob implements ShouldQueue
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
            $postback = $this->event['postback'] ?? [];
            $payload = $postback['payload'] ?? '';
            $title = $postback['title'] ?? $payload;

            if (!$sender) {
                Log::error('Facebook postback missing sender ID', ['event' => $this->event]);
                return;
            }

            // Find customer
            $customer = Customer::whereHas('socialAccounts', function ($query) use ($sender) {
                $query->where('platform_account_id', $sender)
                    ->where('provider_name', 'facebook');
            })->first();

            if (!$customer) {
                Log::warning('Customer not found for Facebook postback', ['sender_id' => $sender]);
                return;
            }

            // Find conversation
            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::FACEBOOK->value,
                platformConversationId: $sender
            );

            // Create message for postback
            $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: "Button clicked: {$title}",
                type: MessageTypeEnum::TEXT->value,
                platformMessageId: null,
                metadata: [
                    'postback_payload' => $payload,
                    'postback_title' => $title,
                    'timestamp' => $this->event['timestamp'] ?? null,
                ]
            );

            Log::info('Facebook postback processed', [
                'conversation_id' => $conversation->id,
                'payload' => $payload,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Facebook postback', [
                'error' => $e->getMessage(),
                'event' => $this->event,
            ]);
            throw $e;
        }
    }
}

