<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationDetailResource extends JsonResource
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
            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
                'status' => $this->customer->status,
                'country' => $this->customer->country,
                'city' => $this->customer->city,
            ],
            'platform' => $this->platform,
            'platform_conversation_id' => $this->platform_conversation_id,
            'status' => $this->status,
            'channel_type' => $this->channel_type,
            'assigned_to' => $this->assigned_to,
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return $this->assignedUser ? [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ] : null;
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'color' => $tag->color,
                        'description' => $tag->description,
                    ];
                });
            }),
            'notes' => $this->whenLoaded('notes', function () {
                return $this->notes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'content' => $note->content,
                        'is_pinned' => $note->is_pinned,
                        'user' => [
                            'id' => $note->user->id,
                            'name' => $note->user->name,
                        ],
                        'created_at' => $note->created_at->toISOString(),
                    ];
                });
            }),
            'message_count' => $this->message_count,
            'unread_count' => $this->unread_count,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'first_response_at' => $this->first_response_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

