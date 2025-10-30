<?php

namespace Database\Seeders\Landlord;

use App\Enum\ExternalPlatformEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlatformsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('landlord')->table('platforms')->insert([
            [
                'slug' => Str::slug(ExternalPlatformEnum::FACEBOOK->value),
                'name' => json_encode([
                    'ar' => 'ماسنجر',
                    'en' => 'Messenger',
                    'fr' => 'Messager',
                    'es' => 'Mensajero',
                ]),
                'is_active' => true,
                'base_url' => 'https://graph.facebook.com',
                'auth_url' => 'https://www.facebook.com/v23.0/dialog/oauth',
                'token_url' => 'https://graph.facebook.com/v23.0/oauth/access_token',
                'api_version' => 'v23.0',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'slug' => Str::slug(ExternalPlatformEnum::SHOPIFY->value),
                'display_name' => json_encode([
                    'ar' => 'شوبيفاي',
                    'en' => 'Shopify',
                    'fr' => 'Shopify',
                    'es' => 'Shopify',
                ]),
                'is_active' => true,
                'base_url' => 'https://{shop}.myshopify.com',
                'auth_url' => 'https://{shop}.myshopify.com/admin/oauth/authorize',
                'token_url' => 'https://{shop}.myshopify.com/admin/oauth/access_token',
                'api_version' => '2025-07',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}
