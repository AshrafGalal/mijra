<?php

namespace App\Http\Resources\Tenant\Conversation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_forward' => $this->is_forward,
            'message_type' => $this->message_type, // text, image, video, audio, location, sticker, document, contact,
            'direction' => $this->direction,
            'status' => $this->status,
            'sender' => $this->contact_name,
            'body' => $this->body,
            'has_media' => $this->has_media,
            'reply_to_message_id' => $this->reply_to_message_id,
            'received_at' => $this->received_at,
            'sent_at' => $this->sent_at,
            'delivered_at' => $this->delivered_at,
            'read_at' => $this->read_at,
            'emoji' => $this->emoji,
            'message' => ConversationMessageResource::make($this->whenLoaded('replyTo')),
        ];
    }
}
