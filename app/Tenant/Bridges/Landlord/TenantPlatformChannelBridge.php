<?php


namespace App\Tenant\Bridges\Landlord;

use App\Services\Landlord\TenantPlatformChannelService;
use Illuminate\Support\Collection;

readonly class TenantPlatformChannelBridge
{
    public function __construct(protected TenantPlatformChannelService $tenantPlatformChannelService)
    {
    }

    public function createOrUpdateMany($tenantChannelDTOs): Collection
    {
        return $this->tenantPlatformChannelService->createOrUpdateMany($tenantChannelDTOs);
    }

}
