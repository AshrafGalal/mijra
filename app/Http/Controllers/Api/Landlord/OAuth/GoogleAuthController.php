<?php

namespace App\Http\Controllers\Api\Landlord\OAuth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\AuthUserResource;
use App\Services\Landlord\Actions\Auth\GoogleAuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(protected readonly GoogleAuthService $googleAuthService) {}

    public function redirectToProvider(string $provider = 'google')
    {
        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return ApiResponse::success(data: ['url' => $url]);
    }

    public function authenticate(Request $request)
    {
        try {

            if ($request->get('access_token') == null || $request->get('access_token') == '') {
                return ApiResponse::badRequest(message: 'Access token is missing');
            }

            $user = $this->googleAuthService->handle(access_token: $request->access_token);

            $currentTenant = $user->tenant;

            $currentTenant->makeCurrent();

            // Create tenant-specific token
            $token = $user->generateToken(name: 'multi-tenant-access', abilities: ['tenant:'.$currentTenant->id]);

            $data = [
                'token' => $token,
                'user' => AuthUserResource::make($user),
                'tenant' => [
                    'name' => $currentTenant->name,
                    'slug' => $currentTenant->slug,
                ],
            ];

            return ApiResponse::success(data: $data);

        } catch (\Exception $e) {
            return ApiResponse::error(message: 'there is an error please try again later or contact with support for fast response');
        }
    }
}
