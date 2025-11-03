<?php

namespace App\Jobs;

use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\MessageDTO;
use App\Models\Tenant\Message;
use App\Services\Tenant\ConversationService;
use App\Services\Tenant\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncWhatsappChatsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ConversationDTO $conversationDTO)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ConversationService $conversationService, MessageService $messageService): void
    {
        $conversationDTO = $this->conversationDTO;
        // Get or create conversation
        $conversation = $conversationService->firstOrCreate(conversationDTO: $this->conversationDTO);
        $plainMessages = [];
        $messagesWithMedia = [];
        foreach ($conversationDTO->messages as $msg) {
            $messageDTO = new MessageDTO(
                conversation_id: $conversation->id,
                external_message_id: $msg['external_message_id'] ?? null,
                sender: $msg['sender'],
                receiver: $msg['receiver'],
                reply_to_external_message_id: $msg['reply_to_message_id'] ?? null,
                body: $msg['body'] ?? null,
                direction: $msg['direction'],
                is_forward: $msg['is_forward'] ?? false,
                has_media: $msg['has_media'] ?? false,
                sent_at: \Carbon\Carbon::parse($msg['sent_at'])->addSeconds()->format('Y-m-d H:i:s'),
                mediaData: $msg['mediaData'] ?? null,
                platform_account_id: $conversationDTO->platform_account_id,
                status: $msg['status'] ?? null,
            );
            if (! empty($messageDTO->mediaData)) {
                $messagesWithMedia[] = $messageDTO;
            } else {
                $plainMessages[] = $messageDTO->toArrayExcept(['mediaData']);
            }
        }

        // 1️⃣ Bulk insert for plain messages
        if (! empty($plainMessages)) {
            $messageService->bulkCreate($plainMessages);
        }

        // 2️⃣ Handle media messages one by one (rare but necessary)
        foreach ($messagesWithMedia as $dto) {
            $messageService->create($dto);
        }

        // 3️⃣ Update conversation’s last message
        $latestMessage = Message::query()
            ->where('conversation_id', $conversation->id)
            ->latest('sent_at')
            ->first();

        // 3️⃣ Update conversation once
        if ($latestMessage) {
            $conversation->update([
                'last_message_at' => $latestMessage->sent_at->addSeconds(2),
                'last_message_id' => $latestMessage->id,
            ]);
        }

    }
}
