<?php

namespace App\Services\Tenant\Actions\User;

use App\DTOs\Tenant\TenantUserDTO;
use App\Models\Tenant\Filters\UsersFilter;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateTenantUserService extends BaseService
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

    public function handle(TenantUserDTO $tenantUserDTO, $tenant_user_id): ?Model
    {
        $tenantUser = $this->findById(id: $tenant_user_id);

        $this->startTransaction();

        // update landlord user;
        $this->updateLandlordUser(dto: $tenantUserDTO, tenantUser: $tenantUser);

        // set email verfied at

        $tenantUser->update($tenantUserDTO->toArrayOnly(only: ['name', 'email', 'phone', 'department_id']));

        $role = Role::query()->find($tenantUserDTO->role_id);

        // This will remove all old roles and assign the new one
        $tenantUser->syncRoles([$role->name]); // as for now user has one role

        $this->commitTransaction();

        return $tenantUser;
    }

    private function updateLandlordUser(TenantUserDTO $dto, User $tenantUser): bool
    {
        $landlordUser = \App\Models\Landlord\User::find($tenantUser->landlord_user_id);

        return $landlordUser->update([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
        ]);
    }

    private function startTransaction(): void
    {
        DB::connection('landlord')->beginTransaction();
        DB::connection('tenant')->beginTransaction();
    }

    private function commitTransaction(): void
    {
        DB::connection('tenant')->commit();
        DB::connection('landlord')->commit();
    }
}
