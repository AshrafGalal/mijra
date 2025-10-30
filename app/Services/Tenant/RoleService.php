<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Role;
use App\Services\BaseRoleService;
use Illuminate\Database\Eloquent\Builder;

class RoleService extends BaseRoleService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Role::query();
    }
}
