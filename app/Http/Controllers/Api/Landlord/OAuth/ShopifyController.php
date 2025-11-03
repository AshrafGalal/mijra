<?php

namespace App\Http\Controllers\Api\Landlord\OAuth;

use App\DTOs\Landlord\ShopifyCallbackDTO;
use App\Enum\ExternalPlatformEnum;
use App\Exceptions\ShopifyOAuthException;
use App\Http\Controllers\Controller;
use App\Services\Landlord\OAuth\ShopifyOAuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class ShopifyController extends Controller
{
    public function __construct(protected readonly ShopifyOAuthService $shopifyOAuthService) {}

    public function redirectToProvider()
    {
        $tenant_id = auth()->user()->tenant_id;

        return Socialite::driver(ExternalPlatformEnum::SHOPIFY->value)
            ->with(['state' => $tenant_id]) // attach tenant id
            ->scopes(config('services.shopify.scopes'))
            ->stateless()
            ->redirect()
            ->getTargetUrl();

    }

    public function callback(Request $request)
    {
        try {
            $dto = ShopifyCallbackDTO::fromRequest($request);
            $payload = $this->shopifyOAuthService->handleCallback($dto);
        } catch (ShopifyOAuthException $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [],
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => [],
            ];
        }

        $encoded = base64_encode(json_encode($payload));

        return redirect()->away(config('app.frontend_url')."/shopify/callback?data={$encoded}");
    }
}
