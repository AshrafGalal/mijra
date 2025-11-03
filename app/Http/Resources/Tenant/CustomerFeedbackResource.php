<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerFeedbackResource extends JsonResource
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
            'email' => $this->email,
            'feedbackCategory' => FeedbackCategoryResource::make($this->whenLoaded('feedbackCategory')),
            'rating' => $this->rating,
            'detailed_review' => $this->detailed_review,
            'source' => $this->source->value,
            'source_text' => $this->source->getLabel(),
            'status' => $this->status->value,
            'status_text' => $this->status->getLabel(),
            'created_at' => $this->created_at->format('Y-m-d h:i A'),
        ];
    }
}
