<?php

namespace App\Jobs;

use App\Models\Landlord\Platform;
use App\Models\Landlord\TenantPlatform;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class UpgradeFacebookTokenJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $tenant_id, public string $short_lived_access_token, public Platform $platform)
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
            'fb_exchange_token' => $this->short_lived_access_token,
        ]);

        if ($longLivedResponse->ok()) {
            $data = $longLivedResponse->json();
            TenantPlatform::query()
                ->where('platform_id', $platform->id)
                ->where('tenant_id', $this->tenant_id)
                ->update([
                    'access_token' => $data['access_token'],
                    'expires_at' => now()->addSeconds($data['expires_in']),
                    'status' => 'active',
                ]);
        }
    }
}
