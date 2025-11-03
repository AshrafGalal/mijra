<?php

namespace App\Http\Controllers\Api\Tenant\Channels;

use App\DTOs\Landlord\TenantPlatformChannelDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSelectedPagesRequest;
use App\Http\Resources\Landlord\TenantPlatformChannelResource;
use App\Http\Resources\Tenant\Facebook\FacebookPageResource;
use App\Tenant\Bridges\Landlord\FacebookBridge;
use App\Tenant\Bridges\Landlord\TenantPlatformChannelBridge;
use App\Tenant\Bridges\Landlord\TenantPlatformConnectionBridge;
use Illuminate\Http\Client\ConnectionException;

class FacebookController extends Controller
{
    public function __construct(
        protected readonly FacebookBridge                 $facebookBridge,
        protected readonly TenantPlatformChannelBridge    $tenantPlatformChannelBridge,
        protected readonly TenantPlatformConnectionBridge $tenantPlatformConnectionBridge)
    {
    }

    /**
     * @throws ConnectionException
     */
    public function pages()
    {
        $pages = $this->facebookBridge->getPages();
        return FacebookPageResource::collection($pages);
    }


    public function storeSelectedPages(StoreSelectedPagesRequest $request)
    {
        $tenantPlatformConnection = $this->tenantPlatformConnectionBridge->getTenantPlatformConnection(platform: $request->platform_slug);

        if (!$tenantPlatformConnection) {
            return ApiResponse::badRequest(message: 'there is no connection established !');
        }

        $tenantPlatformChannelsDTOS = collect($request->validated('pages'))
            ->map(fn($page) => TenantPlatformChannelDTO::fromArray([
                ...$page,
                'tenant_id' => $tenantPlatformConnection->tenant_id,
                'platform_id' => $tenantPlatformConnection->platform_id,
                'tenant_platform_connection_id' => $tenantPlatformConnection->id,
            ]));

        $channels = $this->tenantPlatformChannelBridge->createOrUpdateMany($tenantPlatformChannelsDTOS);

        return TenantPlatformChannelResource::collection($channels);
    }
}
