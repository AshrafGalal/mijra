<?php

namespace App\Services\Tenant\Actions\User;

use App\Models\Tenant\Filters\UsersFilter;
use App\Models\Tenant\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DeleteTenantUserService extends BaseService
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

    public function handle(int $tenantUserId)
    {

        $tenantUser = $this->findById($tenantUserId);
        // get current landlord for auth user
        $landlordUser = \App\Models\Landlord\User::query()
            ->where('id', $tenantUser->landlord_user_id)
            ->first();

        // check if tenant has availability to create user
        $tenant = $landlordUser->tenant;

        $this->startTransaction();

        $tenant->releaseFeatureUsage(slug: 'max-users');

        $tenantUser->delete();
        // delete landlord user;
        $landlordUser->delete();

        $this->commitTransaction();

        return $tenantUser;
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
