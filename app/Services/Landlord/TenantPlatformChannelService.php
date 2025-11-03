<?php

namespace App\Services\Landlord;

use App\DTOs\Landlord\TenantPlatformChannelDTO;
use App\DTOs\Landlord\TenantPlatformConnectionDTO;
use App\Models\Landlord\TenantPlatformChannel;
use App\Models\Landlord\TenantPlatformConnection;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantPlatformChannelService extends BaseService
{

    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return TenantPlatformChannel::query();
    }

    public function save(TenantPlatformChannelDTO $tenantPlatformChannelDTO): Model
    {
        // Upsert or create a new record
        return $this->baseQuery()->updateOrCreate(
            [
                // Match existing channel
                'tenant_id' => $tenantPlatformChannelDTO->tenant_id,
                'platform_id' => $tenantPlatformChannelDTO->platform_id,
                'external_id' => $tenantPlatformChannelDTO->external_id,
            ],
            [
                // Update or set these fields
                'tenant_platform_connection_id' => $tenantPlatformChannelDTO->tenant_platform_connection_id,
                'name' => $tenantPlatformChannelDTO->name,
                'access_token' => $tenantPlatformChannelDTO->access_token,
                'category' => $tenantPlatformChannelDTO->category,
                'category_list' => $tenantPlatformChannelDTO->category_list,
                'capabilities' => $tenantPlatformChannelDTO->capabilities,
                'meta' => $tenantPlatformChannelDTO->meta,
                'settings' => $tenantPlatformChannelDTO->settings,
                'status' => $tenantPlatformChannelDTO->status ?? 'active',
                'last_synced_at' => now(),
            ]
        );
    }

    /**
     * @param Collection<int,TenantPlatformChannelData>|array $dtos
     */
    public function createOrUpdateMany(Collection|array $dtos): Collection
    {
        $dtos = collect($dtos);
        $now = now();

        $rows = $dtos->map(fn(TenantPlatformChannelDTO $dto) => [
            ...$dto->toArray(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->getQuery()
            ->upsert(
                $rows->toArray(),
                ['tenant_id', 'platform_id', 'external_id'],
                [
                    'name',
                    'access_token',
                    'category',
                    'category_list',
                    'capabilities',
                    'meta',
                    'settings',
                    'status',
                    'updated_at',
                    'last_synced_at',
                ]
            );

        // Return updated/inserted models
        $first = $dtos->first();
        return $this->getQuery()
            ->where('tenant_id', $first->tenant_id)
            ->where('platform_id', $first->platform_id)
            ->whereIn('external_id', $dtos->pluck('external_id'))
            ->get();
    }
}
