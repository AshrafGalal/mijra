<?php

namespace App\Http\Resources\Tenant\Conversation\Story;

use App\Http\Resources\Tenant\Media\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
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
            'body' => $this->body,
            'platform' => $this->platform,
            'has_media' => $this->has_media,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'contact_name' => $this->contact_name,
            'media' => MediaResource::collection($this->whenLoaded('media')),

        ];
    }
}
