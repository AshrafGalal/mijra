<?php

namespace App\Http\Resources\Tenant\Opportunity;

use App\Http\Resources\Tenant\CustomerResource;
use App\Http\Resources\Tenant\StageResource;
use App\Http\Resources\Tenant\WorkflowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
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
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'workflow' => WorkflowResource::make($this->whenLoaded('workflow')),
            'stage' => StageResource::make($this->whenLoaded('stage')),
            'user' => $this->when(
                $this->relationLoaded('user'),
                fn () => [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'phone' => $this->user?->phone,
                ],
            ),
            'priority' => $this->priority->value,
            'priority_text' => $this->priority->getLabel(),
            'status' => $this->status->value,
            'status_text' => $this->status->getLabel(),
            'notes' => $this->notes,
            'total_amount' => $this->whenAggregated('OpportunityItems', 'total', 'sum'),
            'expected_close_date' => $this->expected_close_date,
        ];
    }
}
