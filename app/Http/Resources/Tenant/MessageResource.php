<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'platform_message_id' => $this->platform_message_id,
            'direction' => $this->direction,
            'type' => $this->type,
            'content' => $this->content,
            'sender_type' => $this->sender_type,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ] : null;
            }),
            'status' => $this->status,
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'type' => $attachment->type,
                        'url' => $attachment->url,
                        'filename' => $attachment->filename,
                        'mime_type' => $attachment->mime_type,
                        'file_size' => $attachment->file_size,
                        'formatted_size' => $attachment->formatted_size,
                        'width' => $attachment->width,
                        'height' => $attachment->height,
                        'duration' => $attachment->duration,
                        'thumbnail_url' => $attachment->thumbnail_url,
                    ];
                });
            }),
            'metadata' => $this->metadata,
            'delivered_at' => $this->delivered_at?->toISOString(),
            'read_at' => $this->read_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'error_message' => $this->error_message,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

