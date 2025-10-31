<?php

namespace App\Events;

use App\Models\Tenant\ConversationTransfer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ConversationTransfer $transfer
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("conversations.{$this->transfer->conversation_id}"),
            new PrivateChannel("users.{$this->transfer->from_user_id}"),
            new PrivateChannel("users.{$this->transfer->to_user_id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.transferred';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->transfer->conversation_id,
            'from_user_id' => $this->transfer->from_user_id,
            'to_user_id' => $this->transfer->to_user_id,
            'from_user' => [
                'id' => $this->transfer->fromUser->id,
                'name' => $this->transfer->fromUser->name,
            ],
            'to_user' => [
                'id' => $this->transfer->toUser->id,
                'name' => $this->transfer->toUser->name,
            ],
            'reason' => $this->transfer->reason,
            'transferred_at' => $this->transfer->transferred_at->toISOString(),
        ];
    }
}

