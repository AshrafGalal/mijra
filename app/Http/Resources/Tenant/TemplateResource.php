<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'status' => $this->status->value,
            'status_text' => $this->status->getLabel(),
            'description' => $this->description,
            'template_type' => $this->template_type->value,
            'template_type_text' => $this->template_type->getLabel(),
            'category' => $this->category,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i A') : null,
        ];
    }
}
