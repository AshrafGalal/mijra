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

class ProcessInstagramReactionJob implements ShouldQueue
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
            $reaction = $this->event['reaction'] ?? [];
            $emoji = $reaction['emoji'] ?? '👍';
            $mid = $reaction['mid'] ?? null;

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
                content: "Reacted with: {$emoji}",
                type: MessageTypeEnum::TEXT->value,
                platformMessageId: null,
                metadata: [
                    'reaction' => $emoji,
                    'message_id' => $mid,
                    'is_reaction' => true,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error processing Instagram reaction', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

