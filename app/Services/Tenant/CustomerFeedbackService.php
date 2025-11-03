<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\CustomerFeedbackDTO;
use App\Models\Tenant\CustomerFeedback;
use App\Models\Tenant\Filters\CustomerFeedbackFilters;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerFeedbackService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return CustomerFeedbackFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return CustomerFeedback::query();
    }

    public function create(CustomerFeedbackDTO $dto): CustomerFeedback
    {
        return $this->baseQuery()->create($dto->toArray());
    }

    public function update(CustomerFeedback|int $customerFeedback, CustomerFeedbackDTO $dto): CustomerFeedback
    {
        if (is_int($customerFeedback)) {
            $customerFeedback = parent::findById($customerFeedback);
        }
        $customerFeedback->update($dto->toArray());

        return $customerFeedback;
    }

    public function delete(CustomerFeedback|int $customerFeedback): ?bool
    {
        if (is_int($customerFeedback)) {
            $customerFeedback = parent::findById($customerFeedback);
        }

        return $customerFeedback->delete();
    }

    public function paginate(?array $filters = [], int $limit = 15, ?array $withRelations = []): LengthAwarePaginator
    {
        return $this->getQuery(filters: $filters, withRelation: $withRelations)
            ->orderByDesc('id')
            ->paginate($limit);
    }

    public function list(?array $filters = []): Collection
    {
        return $this->getQuery($filters)
            ->orderByDesc('id')
            ->get();
    }

    public function statics(): ?object
    {
        return $this->baseQuery()
            ->selectRaw('COUNT(*) as total_feedback')
            ->selectRaw('AVG(rating) as avg_rating')
            ->selectRaw('
                                ROUND(
                                    (SUM(CASE WHEN status IN ("responded", "escalated", "resolved") THEN 1 ELSE 0 END) / COUNT(*)) * 100,
                                    2
                                ) as respond_rate_percentage
                            ')
            ->selectRaw('SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new_rate_count')
            ->first();
    }
}
