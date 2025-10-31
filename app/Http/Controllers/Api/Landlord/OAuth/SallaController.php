<?php

namespace App\Http\Controllers\Api\Landlord\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SallaController extends Controller
{
    /**
     * Redirect to Salla OAuth.
     */
    public function redirectToProvider()
    {
        $tenantId = auth()->user()->tenant_id;
        $clientId = config('services.salla.client_id');
        $redirectUri = config('services.salla.redirect');
        $baseUrl = config('services.salla.base_url');

        $url = "{$baseUrl}/oauth2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $tenantId,
            'scope' => 'offline_access',
        ]);

        return response()->json(['authorization_url' => $url]);
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state'); // tenant_id

        if (!$code || !$state) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        try {
            // Exchange code for access token
            $response = Http::asForm()->post(config('services.salla.base_url') . '/oauth2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.salla.client_id'),
                'client_secret' => config('services.salla.client_secret'),
                'redirect_uri' => config('services.salla.redirect'),
                'code' => $code,
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to exchange code for token');
            }

            $data = $response->json();
            $accessToken = $data['access_token'] ?? null;
            $refreshToken = $data['refresh_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            // Get merchant info
            $merchantInfo = $this->getMerchantInfo($accessToken);

            // Store in tenant_platforms
            DB::connection('landlord')->table('tenant_platforms')->updateOrInsert(
                [
                    'tenant_id' => $state,
                    'platform_id' => $this->getSallaPlatformId(),
                ],
                [
                    'external_id' => $merchantInfo['id'] ?? null,
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_at' => now()->addSeconds($expiresIn),
                    'meta' => json_encode($merchantInfo),
                    'status' => 'active',
                    'updated_at' => now(),
                ]
            );

            Log::info('Salla OAuth successful', ['tenant_id' => $state]);

            $encoded = base64_encode(json_encode([
                'success' => true,
                'message' => 'Salla integrated successfully',
            ]));

            return redirect()->away(config('app.frontend_url') . "/salla/callback?data={$encoded}");

        } catch (\Exception $e) {
            Log::error('Salla OAuth error', ['error' => $e->getMessage()]);

            $encoded = base64_encode(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));

            return redirect()->away(config('app.frontend_url') . "/salla/callback?data={$encoded}");
        }
    }

    /**
     * Get merchant info from Salla.
     */
    protected function getMerchantInfo(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get(config('services.salla.base_url') . '/oauth2/user/info');

            return $response->successful() ? $response->json() : [];

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Salla platform ID.
     */
    protected function getSallaPlatformId(): int
    {
        return DB::connection('landlord')
            ->table('platforms')
            ->where('slug', 'salla')
            ->value('id') ?? 1;
    }
}

