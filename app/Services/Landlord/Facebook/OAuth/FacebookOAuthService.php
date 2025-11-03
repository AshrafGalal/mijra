<?php

namespace App\Services\Landlord\Facebook\OAuth;

use App\DTOs\Landlord\TenantPlatformConnectionDTO;
use App\Enum\ExternalPlatformEnum;
use App\Exceptions\FacebookOAuthException;
use App\Jobs\UpgradeFacebookTokenJob;
use App\Models\Landlord\Platform;
use App\Models\Landlord\Tenant;
use App\Services\Landlord\Facebook\FacebookService;
use App\Services\Landlord\TenantPlatformConnectionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class FacebookOAuthService
{
    public function __construct(
        protected readonly FacebookService                 $facebookService,
        protected readonly TenantPlatformConnectionService $tenantPlatformConnectionService
    )
    {
    }

    /**
     * Attempt to login with credentials and return user or unauthorized exception.
     *
     * @throws UnauthorizedHttpException
     * @throws FacebookOAuthException
     * @throws ConnectionException
     */
    public function handle(string $tenantId, string $fbCode): Model
    {
        $platform = $this->facebookService->getPlatform();
        $tenant = Tenant::find($tenantId);

        if (!$platform || !$tenant) {
            throw new FacebookOAuthException(
                !$platform ? 'Platform not found' : 'Tenant not found'
            );
        }

        $accessData = $this->getAccessData($platform, $fbCode);
        if (empty($accessData)) {
            throw new FacebookOAuthException('cannot get access token please try again later !');
        }

        // Build DTO
        $dto = new TenantPlatformConnectionDTO(
            tenant_id: $tenant->id,
            platform_id: $platform->id,
            user_access_token: $accessData['access_token'],
            refresh_token: $accessData['refresh_token'] ?? null,
            token_expires_at: isset($accessData['expires_in'])
                ? now()->addSeconds($accessData['expires_in'])
                : null,
            credentials: $accessData,
            meta: ['source' => 'facebook_oauth'],
        );
        // Create platform connection
        $tenantPlatformConnection = $this->tenantPlatformConnectionService->save(tenantPlatformConnectionDTO: $dto);

        // Queue token upgrade job
        dispatch(new UpgradeFacebookTokenJob(
            tenantPlatformConnection: $tenantPlatformConnection,
            platform: $platform
        ));

        return $tenantPlatformConnection;
    }

    /**
     * @return array|mixed
     *
     * @throws ConnectionException
     * @throws FacebookOAuthException
     */
    private function getAccessData(Platform $platform, string $fb_code): mixed
    {
        $response = Http::get($platform->token_url, [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => config('services.facebook.redirect'),
            'code' => $fb_code,
        ]);

        if ($response->failed()) {
            throw new FacebookOAuthException('Failed to exchange code for access token.');
        }

        return $response->json();
    }
}
