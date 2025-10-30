<?php

namespace App\Http\Resources\Tenant;

use App\Http\Resources\Tenant\Media\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status->value,
            'status_text' => $this->status->getLabel(),
            'priority' => $this->priority->value,
            'priority_text' => $this->priority->getLabel(),
            'due_date' => $this->due_date,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i A') : null,
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'customer' => $this->when(
                $this->relationLoaded('customer'),
                fn () => [
                    'id' => $this->customer?->id,
                    'name' => $this->customer?->name,
                    'phone' => $this->customer?->phone,
                ],
            ),
            'assignedTo' => $this->when(
                $this->relationLoaded('user'),
                fn () => [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'phone' => $this->user?->phone,
                ],
            ),
        ];
    }
}
