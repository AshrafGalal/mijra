<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\WorkflowDTO;
use App\Models\Tenant\Workflow;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WorkflowService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Workflow::query();
    }

    public function create(WorkflowDTO $dto): Workflow
    {
        return $this->baseQuery()->create($dto->toArray());
    }

    public function update(Workflow|int $pipeline, WorkflowDTO $dto): Workflow
    {
        if (is_int($pipeline)) {
            $pipeline = parent::findById($pipeline);
        }
        $pipeline->update($dto->toArray());

        return $pipeline;
    }

    public function delete(Workflow|int $pipeline): ?bool
    {
        if (is_int($pipeline)) {
            $pipeline = parent::findById($pipeline);
        }

        return $pipeline->delete();
    }

    public function getPipelines(?array $filters = [], array $withRelations = [], array $countRelations = []): Collection
    {
        return $this->getQuery(filters: $filters, withRelation: $withRelations)
            ->withCount($countRelations)
            ->orderByDesc('id')
            ->get();
    }

    public function move(int $pipelineId, string $direction): void
    {
        DB::connection('tenant')
            ->transaction(function () use ($pipelineId, $direction) {
                $pipeline = parent::findById($pipelineId);

                $operator = $direction == 'up' ? '<' : '>';
                $order = $direction == 'up' ? 'desc' : 'asc';

                $swapTarget = $this->getQuery()
                    ->where('sort_order', $operator, $pipeline->sort_order)
                    ->orderBy('sort_order', $order)
                    ->first();

                if (! $swapTarget) {
                    return; // can't move further
                }

                // Swap their sort_order
                [$pipeline->sort_order, $swapTarget->sort_order] = [$swapTarget->sort_order, $pipeline->sort_order];

                $pipeline->save();
                $swapTarget->save();
            });
    }
}
