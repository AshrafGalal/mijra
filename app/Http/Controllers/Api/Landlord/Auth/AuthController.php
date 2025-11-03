<?php

namespace App\Http\Controllers\Api\Landlord\Auth;

use App\DTOs\Landlord\AuthCredentialsDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\AuthFormRequest;
use App\Http\Resources\Tenant\AuthUserResource;
use App\Services\Landlord\Actions\Auth\AuthService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function __invoke(AuthFormRequest $request, AuthService $authService)
    {
        try {
            $credentials = AuthCredentialsDTO::fromRequest($request);
            $user = $authService->authenticate($credentials);

            $tenant = $user->tenant;
            if (! $tenant || ! $tenant->status) {
                return response()->json(['error' => 'Tenant not active'], 403);
            }

            $tenant->makeCurrent();

            // Create tenant-specific token
            $token = $user->generateToken(name: 'multi-tenant-access', abilities: ['tenant:'.$tenant->id]);

            $data = [
                'token' => $token,
                'user' => AuthUserResource::make($user),
            ];

            return ApiResponse::success(data: $data);
        } catch (UnauthorizedHttpException $e) {
            return ApiResponse::unauthorized(__('auth.failed'), []);
        }
    }
}
