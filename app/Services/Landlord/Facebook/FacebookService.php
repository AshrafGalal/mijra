<?php

namespace App\Services\Landlord\Facebook;

use App\Enum\ExternalPlatformEnum;
use App\Models\Landlord\Platform;
use App\Services\Landlord\TenantPlatformConnectionService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FacebookService
{
    public function __construct(protected readonly TenantPlatformConnectionService $tenantPlatformConnectionService)
    {
    }

    public function getPlatform(): Platform|null
    {
        return Platform::query()->firstWhere('slug', ExternalPlatformEnum::MESSENGER->value);
    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public function getPages(): array
    {
        $platform = $this->getPlatform();
        $tenantConnection = $this->tenantPlatformConnectionService->getTenantPlatformConnection(platform: $platform);

        if (!$tenantConnection) {
            throw new NotFoundHttpException('Connection not found, Please connect to platform first');
        }
        $response = Http::get($platform->url . '/me/accounts', [
            'access_token' => $tenantConnection->user_access_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get pages');
        }

        return $response->json()['data'];
    }
}
