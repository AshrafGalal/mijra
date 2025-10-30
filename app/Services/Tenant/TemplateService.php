<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Filters\TemplateFilters;
use App\Models\Tenant\Template;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class TemplateService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return TemplateFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Template::query();
    }

    public function paginate(array $filters = [], $limit = 15): LengthAwarePaginator
    {
        return $this->getQuery($filters)->paginate($limit);
    }

    public function delete(int $id): bool
    {
        $template = $this->findById($id);

        return $template->delete();
    }
}
