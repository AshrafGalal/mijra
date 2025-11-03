<?php

namespace App\Http\Resources\Tenant\Opportunity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityStageResource extends JsonResource
{
    public function __construct($resource, protected $groupedOpportunities)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stageOpportunities = $this->groupedOpportunities->get($this->id, collect());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sort_order' => $this->sort_order,
            'opportunity_count' => $stageOpportunities->count(),
            'total_amount' => $stageOpportunities->sum('amount'),
            'opportunities' => OpportunityResource::collection($stageOpportunities),
        ];
    }
}
