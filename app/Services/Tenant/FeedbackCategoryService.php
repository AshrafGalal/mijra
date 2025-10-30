<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\FeedbackCategoryDTO;
use App\Models\Tenant\FeedbackCategory;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class FeedbackCategoryService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return FeedbackCategory::query();
    }

    public function create(FeedbackCategoryDTO $dto): FeedbackCategory
    {
        return $this->baseQuery()->create($dto->toArray());
    }

    public function update(FeedbackCategory|int $feedbackCategory, FeedbackCategoryDTO $dto): FeedbackCategory
    {
        if (is_int($feedbackCategory)) {
            $feedbackCategory = parent::findById($feedbackCategory);
        }
        $feedbackCategory->update($dto->toArray());

        return $feedbackCategory;
    }

    public function delete(FeedbackCategory|int $feedbackCategory): ?bool
    {
        if (is_int($feedbackCategory)) {
            $feedbackCategory = parent::findById($feedbackCategory);
        }

        return $feedbackCategory->delete();
    }

    public function paginate(?array $filters = [], int $limit = 15)
    {
        return $this->getQuery($filters)->paginate($limit);
    }

    public function list(?array $filters = [])
    {
        return $this->getQuery($filters)->get();
    }
}
