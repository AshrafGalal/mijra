<?php

namespace App\Events;

use App\Models\Tenant\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    public function broadcastOn(): Channel
    {
        // You can use PrivateChannel if you have auth
        return new PrivateChannel("conversations.{$this->message->conversation_id}");
    }

    public function broadcastAs(): string
    {
        return 'message.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'status' => $this->message->status,
        ];
    }
}
