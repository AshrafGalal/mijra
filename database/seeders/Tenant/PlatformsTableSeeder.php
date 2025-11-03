<?php

namespace Database\Seeders\Tenant;

use App\Models\Landlord\Platform;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlatformsTableSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = Platform::all();
        foreach ($platforms as $platform) {
            \App\Models\Tenant\Platform::create([
                'name' => $platform->name,
                'display_name' => $platform->display_name,
                'is_active' => $platform->is_active,
                'api_version' => $platform->api_version,
                'webhook_url' => $platform->webhook_url,
                'settings' => $platform->settings,
            ]);
        }
    }
}
