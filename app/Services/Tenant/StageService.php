<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\StageDTO;
use App\Models\Tenant\Filters\StageFilters;
use App\Models\Tenant\Stage;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StageService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return StageFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Stage::query();
    }

    public function create(StageDTO $dto): Stage
    {
        return $this->baseQuery()->create($dto->toArray());
    }

    public function update(Stage|int $stage, StageDTO $dto): Stage
    {
        if (is_int($stage)) {
            $stage = parent::findById($stage);
        }
        $dto->workflow_id = $stage->workflow_id;
        $stage->update($dto->toArray());

        return $stage;
    }

    public function delete(Stage|int $stage): ?bool
    {
        if (is_int($stage)) {
            $stage = parent::findById($stage);
        }

        return $stage->delete();
    }

    public function getStages(?array $filters = []): Collection
    {
        return $this->getQuery($filters)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @throws \Throwable
     */
    public function move(int $stageId, string $direction): void
    {
        DB::connection('tenant')
            ->transaction(function () use ($stageId, $direction) {
                $stage = parent::findById($stageId);

                $operator = $direction == 'up' ? '<' : '>';
                $order = $direction == 'up' ? 'desc' : 'asc';

                $swapTarget = $this->getQuery()
                    ->where('sort_order', $operator, $stage->sort_order)
                    ->orderBy('sort_order', $order)
                    ->first();

                if (! $swapTarget) {
                    return; // can't move further
                }

                // Swap their sort_order
                [$stage->sort_order, $swapTarget->sort_order] = [$swapTarget->sort_order, $stage->sort_order];

                $stage->save();
                $swapTarget->save();
            });
    }

    public function getStagesByWorkflowId(int $workflowId): Collection
    {
        return $this->getQuery()
            ->where('workflow_id', $workflowId)
            ->orderBy('sort_order')
            ->get();
    }
}
