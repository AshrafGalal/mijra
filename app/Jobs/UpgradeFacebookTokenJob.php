<?php

namespace App\Jobs;

use App\Models\Landlord\Platform;
use App\Models\Landlord\TenantPlatformConnection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class UpgradeFacebookTokenJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public TenantPlatformConnection $tenantPlatformConnection, public Platform $platform)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $platform = $this->platform;

        $longLivedResponse = Http::get($platform->token_url, [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $this->tenantPlatformConnection->user_access_token,
        ]);

        if ($longLivedResponse->ok()) {
            $data = $longLivedResponse->json();
            $this->tenantPlatformConnection
                ->update([
                    'access_token' => $data['access_token'],
                    'expires_at' => now()->addSeconds($data['expires_in']),
                    'status' => 'active',
                ]);
        }
    }
}
