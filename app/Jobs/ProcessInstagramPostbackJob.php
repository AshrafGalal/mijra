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

class ProcessInstagramPostbackJob implements ShouldQueue
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
            $postback = $this->event['postback'] ?? [];
            $payload = $postback['payload'] ?? '';
            $title = $postback['title'] ?? $payload;

            if (!$sender) {
                return;
            }

            $customer = Customer::whereHas('socialAccounts', function ($query) use ($sender) {
                $query->where('platform_account_id', $sender)
                    ->where('provider_name', 'instagram');
            })->first();

            if (!$customer) {
                return;
            }

            $conversation = $conversationService->findOrCreate(
                customerId: $customer->id,
                platform: ExternalPlatformEnum::INSTAGRAM->value,
                platformConversationId: $sender
            );

            $messageService->createInboundMessage(
                conversationId: $conversation->id,
                content: "Button clicked: {$title}",
                type: MessageTypeEnum::TEXT->value,
                platformMessageId: null,
                metadata: [
                    'postback_payload' => $payload,
                    'postback_title' => $title,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error processing Instagram postback', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

