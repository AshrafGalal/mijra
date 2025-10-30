<?php

namespace App\Events;

use App\Models\Tenant\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public int $userId,
        public ?int $previousUserId = null
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel("conversations.{$this->conversation->id}"),
            new PrivateChannel("users.{$this->userId}"),
        ];

        // Also notify previous user if conversation was reassigned
        if ($this->previousUserId) {
            $channels[] = new PrivateChannel("users.{$this->previousUserId}");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.assigned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'assigned_to' => $this->userId,
            'previous_user_id' => $this->previousUserId,
            'customer' => [
                'id' => $this->conversation->customer->id,
                'name' => $this->conversation->customer->name,
            ],
            'platform' => $this->conversation->platform,
            'unread_count' => $this->conversation->unread_count,
        ];
    }
}



