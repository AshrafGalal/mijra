<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Filters\UsersFilter;
use App\Models\Tenant\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService extends BaseService
{
    /**
     * Return the filter class for users.
     */
    protected function getFilterClass(): string
    {
        return UsersFilter::class;
    }

    /**
     * Return the base query for users.
     */
    protected function baseQuery(): Builder
    {
        return User::query();
    }

    public function paginate(?array $filters = [], int $limit = 15): LengthAwarePaginator
    {
        return $this->getQuery(filters: $filters)
            ->with(['roles', 'department'])
            ->orderByDesc('id')
            ->paginate($limit);
    }
}
