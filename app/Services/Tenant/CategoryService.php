<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\CategoryDTO;
use App\Enum\SupportedLocalesEnum;
use App\Models\Tenant\Category;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CategoryService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Category::query();
    }

    /**
     * @throws \Throwable
     */
    public function create(CategoryDTO $dto): Category
    {
        $names = $dto->name;
        // Determine fallback:
        $fallback = Arr::get($names, 'en')
            ?? collect($names)->filter(fn ($v, $k) => $k !== 'ar')->first()
            ?? Arr::get($names, 'ar');

        // Fill missing translations
        foreach (SupportedLocalesEnum::values() as $locale) {
            if (empty($names[$locale])) {
                $names[$locale] = $fallback;
            }
        }

        $dto->name = $names;

        return $this->getQuery()->create($dto->toArray());

    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->getQuery($filters)->paginate();
    }

    public function list(array $filters = [], array $withRelations = [])
    {
        return $this->getQuery($filters)->with($withRelations)->get()->toTree();
    }

    public function update(int $id, CategoryDTO $dto): Model
    {
        $category = $this->findById($id);
        $names = $dto->name;

        // Determine fallback:
        $fallback = Arr::get($names, 'en')
            ?? collect($names)->filter(fn ($v, $k) => $k !== 'ar')->first()
            ?? $names['ar'];

        // Fill missing translations
        foreach (SupportedLocalesEnum::values() as $locale) {
            if (empty($names[$locale])) {
                $names[$locale] = $fallback;
            }
        }

        $dto->name = $names;

        $category->update($dto->toArray());

        return $category;
    }

    public function delete(int $id): bool
    {
        $category = $this->findById($id);

        return $category->delete();
    }
}
