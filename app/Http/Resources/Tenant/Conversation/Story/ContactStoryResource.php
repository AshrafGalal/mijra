<?php

namespace App\Http\Resources\Tenant\Conversation\Story;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ContactStoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contact_identifier_id' => Arr::get($this->resource, 'contact_identifier_id'),
            'contact_name' => Arr::get($this->resource, 'contact_name'),
            'stories' => StoryResource::collection(Arr::get($this->resource, 'stories')),
        ];
    }
}
