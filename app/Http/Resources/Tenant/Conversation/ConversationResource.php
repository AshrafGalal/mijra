<?php

namespace App\Http\Resources\Tenant\Conversation;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'title' => $this->title,
            'platform' => $this->platform,
            'type' => $this->type->value,
            'type_text' => $this->type->typeText(),
            'contact' => $this->contact_name,
            'unread_count' => $this->unread_count,
            'last_message_at' => isset($this->last_message_at) ? Carbon::parse($this->last_message_at)->format('Y-m-d h:i A') : null,
            'latestMessage' => ConversationMessageResource::make($this->whenLoaded('latestMessage')),
        ];
    }
}
