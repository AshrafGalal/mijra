<?php

namespace App\Jobs;

use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\MessageDTO;
use App\Services\Tenant\ConversationService;
use App\Services\Tenant\MessageService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

class ProcessIncomingMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ConversationDTO $conversationDTO)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ConversationService $conversationService, MessageService $messageService): void
    {
        $conversationDTO = $this->conversationDTO;
        // Get or create conversation
        $conversation = $conversationService->firstOrCreate(conversationDTO: $this->conversationDTO);

        $messageData = Arr::first($conversationDTO->messages);
        $messageData['conversation_id'] = $conversation->id;

        $messageDTO = MessageDTO::fromArray($messageData);

        $message = $messageService->create(messageDTO: $messageDTO);

        // update conversation with last message
        $conversation->update([
            'last_message_at' => Carbon::parse($messageDTO->sent_at)
                ->addSeconds(2)->format('Y-m-d H:i:s'),
            'last_message_id' => $message->id,

            'unread_count' => $conversationDTO->unread_count
        ]);
        // Directly set the relation instead of lazy loading
        $conversation->setRelation('latestMessage', $message);

        AssignConversationJob::dispatch($conversation)->onQueue('assignments');

    }
}
