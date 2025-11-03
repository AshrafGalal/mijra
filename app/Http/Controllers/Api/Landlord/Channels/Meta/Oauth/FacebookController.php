<?php

namespace App\Http\Controllers\Api\Landlord\Channels\Meta\Oauth;

use App\Enum\ExternalPlatformEnum;
use App\Exceptions\FacebookOAuthException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Landlord\Facebook\OAuth\FacebookOAuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function __construct(protected readonly FacebookOAuthService $facebookOauthService) {}

    public function redirectToProvider()
    {
        $url = Socialite::driver(ExternalPlatformEnum::MESSENGER->value)
            ->with(['state' => auth()->user()->tenant_id])
            ->scopes(config('services.facebook.scopes'))
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return ApiResponse::success(data: ['url' => $url]);

    }

    public function callback(Request $request)
    {

        try {
            $code = $request->get('code');
            $tenant_id = $request->get('state');

            if (! $code) {
                $payload = [
                    'success' => false,
                    'message' => 'Missing Code to Get Access token',
                    'error' => [],
                ];
                $encoded = base64_encode(json_encode($payload));

                return redirect()->away(config('app.frontend_url')."/facebook/callback?data={$encoded}");
            }

            $payload = $this->facebookOauthService->handle(tenantId: $tenant_id, fbCode: $code);

        } catch (FacebookOAuthException $e) {
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

    public function deleteData(Request $request)
    {
        // dd($request->all());
    }

    public function deAuthorize(Request $request)
    {
        // dd($request->all());
    }
}
