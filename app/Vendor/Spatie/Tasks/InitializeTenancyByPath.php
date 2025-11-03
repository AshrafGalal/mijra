<?php

namespace App\Vendor\Spatie\Tasks;

use App\Models\Landlord\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class InitializeTenancyByPath extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        // Skip tenant resolution for landlord routes
        if ($request->is('api/landlord/*')) {
            return null;
        }
        // Only initialize tenancy if tenant is the first parameter
        $tenantIdentifier = $request->segment(2);

        return Tenant::query()
            ->where(fn ($query) => $query->where('id', $tenantIdentifier)->orWhere('slug', $tenantIdentifier))
            ->first();
    }
}
