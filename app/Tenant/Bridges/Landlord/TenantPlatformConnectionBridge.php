<?php

namespace App\Tenant\Bridges\Landlord;

use App\Services\Landlord\TenantPlatformConnectionService;

readonly class TenantPlatformConnectionBridge
{
    public function __construct(protected TenantPlatformConnectionService $tenantPlatformConnectionService)
    {
    }

    public function getTenantPlatformConnection(string $platform): null
    {
        return $this->tenantPlatformConnectionService->getTenantPlatformConnection($platform);

    }

}
