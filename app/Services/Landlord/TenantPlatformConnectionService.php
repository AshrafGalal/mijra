<?php

namespace App\Services\Landlord;

use App\DTOs\Landlord\TenantPlatformConnectionDTO;
use App\Models\Landlord\Platform;
use App\Models\Landlord\Tenant;
use App\Models\Landlord\TenantPlatformConnection;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantPlatformConnectionService extends BaseService
{

    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return TenantPlatformConnection::query();
    }

    public function save(TenantPlatformConnectionDTO $tenantPlatformConnectionDTO): Model
    {
        // Upsert or create a new record
        return $this->baseQuery()->updateOrCreate(
            [
                'tenant_id' => $tenantPlatformConnectionDTO->tenant_id,
                'platform_id' => $tenantPlatformConnectionDTO->platform_id,
                'external_user_id' => $tenantPlatformConnectionDTO->external_user_id,
            ],
            [
                'user_access_token' => $tenantPlatformConnectionDTO->user_access_token,
                'refresh_token' => $tenantPlatformConnectionDTO->refresh_token,
                'token_expires_at' => $tenantPlatformConnectionDTO->token_expires_at,
                'external_account_id' => $tenantPlatformConnectionDTO->external_account_id,
                'webhook_id' => $tenantPlatformConnectionDTO->webhook_id,
                'webhook_secret' => $tenantPlatformConnectionDTO->webhook_secret,
                'credentials' => $tenantPlatformConnectionDTO->credentials,
                'meta' => $tenantPlatformConnectionDTO->meta,
                'settings' => $tenantPlatformConnectionDTO->settings,
            ]
        );

    }

    public function getTenantPlatformConnection(Platform|string $platform): null
    {
        $tenant = Tenant::current();
        if (is_string($platform)) {
            $platform = Platform::query()->firstWhere('slug', $platform);
        }
        $tenantPlatformConnection = $this->baseQuery()
            ->where('tenant_id', $tenant->id)
            ->where('platform_id', $platform->id)
            ->first();

        if ($tenantPlatformConnection) {
            throw new NotFoundHttpException('Tenant platform connection already exists');
        }
        return $tenantPlatformConnection;
    }
}
