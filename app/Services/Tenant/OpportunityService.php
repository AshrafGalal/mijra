<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\OpportunityDTO;
use App\Models\Tenant\Filters\OpportunityFilters;
use App\Models\Tenant\Opportunity;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OpportunityService extends BaseService
{
    public function __construct(protected readonly WorkflowService $workflowService, protected readonly StageService $stageService) {}

    protected function getFilterClass(): ?string
    {
        return OpportunityFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Opportunity::query();
    }

    public function create(OpportunityDTO $opportunityDTO)
    {
        return DB::connection('tenant')
            ->transaction(function () use ($opportunityDTO) {
                $opportunity = $this->baseQuery()->create($opportunityDTO->toArray());
                $this->createOpportunityItems($opportunity, $opportunityDTO);

                return $opportunity;
            });
    }

    public function delete(Opportunity|int $opportunity): ?bool
    {
        if (is_int($opportunity)) {
            $opportunity = parent::findById($opportunity);
        }

        return $opportunity->delete();
    }

    private function createOpportunityItems(Opportunity $opportunity, OpportunityDTO $opportunityDTO): void
    {
        if (empty($opportunityDTO->items)) {
            return;
        }

        $itemsData = collect($opportunityDTO->items)->map(function ($item) use ($opportunity) {
            $price = (float) $item['price'];
            $quantity = (int) $item['quantity'];
            $discount = isset($item['discount']) ? (float) $item['discount'] : 0;

            return [
                'opportunity_id' => $opportunity->id,
                'item_id' => $item['item_id'],
                'price' => $price,
                'quantity' => $quantity,
                'discount' => $discount,
                'total' => max(0, ($price * $quantity) - $discount), // safe calculation
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        Opportunity::query()->insert($itemsData);
    }

    public function paginate(?array $filters = [], ?array $withRelations = [], $perPage = 15): LengthAwarePaginator
    {
        return $this->getQuery($filters)
            ->with($withRelations)
            ->withSum('OpportunityItems', 'total')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function move(int $opportunityId, int $newStageId): Model|Opportunity
    {
        $opportunity = $this->findById($opportunityId);
        $stage = $this->stageService->findById($newStageId);

        if ($opportunity->workflow_id != $stage->workflow_id) {
            throw new BadRequestException('Stage does not belong to the same pipeline');
        }

        $opportunity->update(['stage_id' => $stage->id]);

        return $opportunity;
    }
}
