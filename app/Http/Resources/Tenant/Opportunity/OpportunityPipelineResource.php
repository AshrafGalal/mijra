<?php

namespace App\Http\Resources\Tenant\Opportunity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityPipelineResource extends JsonResource
{
    public function __construct($resource, protected $stages, protected $opportunities)
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
        $groupedByStage = $this->opportunities->getCollection()->groupBy('stage_id');

        $stagesData = $this->stages->map(function ($stage) use ($groupedByStage) {
            return new OpportunityStageResource($stage, $groupedByStage);
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active->value,
            'is_active_text' => $this->is_active->getLabel(),
            'stages' => $stagesData,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'pagination' => [
                    'current_page' => $this->opportunities->currentPage(),
                    'last_page' => $this->opportunities->lastPage(),
                    'per_page' => $this->opportunities->perPage(),
                    'total' => $this->opportunities->total(),
                    'has_more' => $this->opportunities->hasMorePages(),
                    'has_previous' => $this->opportunities->onFirstPage(),
                    'links' => $this->opportunities->links(),
                ],
            ],
        ];
    }
}
