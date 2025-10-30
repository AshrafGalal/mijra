<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active->value,
            'is_active_text' => $this->is_active->getLabel(),
            'stages_count' => $this->whenCounted('stages'),
            'stages' => StageResource::collection($this->whenLoaded('stages')),
        ];
    }
}
