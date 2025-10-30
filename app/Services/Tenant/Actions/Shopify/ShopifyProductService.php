<?php

namespace App\Services\Tenant\Actions\Shopify;

use App\Enum\ExternalPlatformEnum;
use App\Models\Landlord\Platform;
use App\Models\Landlord\TenantPlatform;

class ShopifyProductService
{


    public function __construct(public Platform|null $platform, public TenantPlatform $tenantPlatform)
    {
        $authUser = auth()->user();

        $this->platform = Platform::query()->firstWhere('slug', ExternalPlatformEnum::SHOPIFY->value);

        $this->tenantPlatform = TenantPlatform::query()
            ->where('slug', ExternalPlatformEnum::SHOPIFY->value)
            ->where('tenant_id', $authUser->tenant_id)
            ->first();
    }

    protected function headers(): array
    {
        return [
            'X-Shopify-Access-Token' => $this->tenantPlatform->access_token,
            'Content-Type' => 'application/json',
        ];
    }

    public function getProducts()
    {

    }

}
