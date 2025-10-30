<?php

namespace Database\Seeders\Landlord;

use App\Enum\ActivationStatusEnum;
use App\Enum\FeatureGroupEnum;
use App\Models\Landlord\Feature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeatureTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'name' => [
                    'ar' => 'الحد الاقصي للمستخدمين',
                    'en' => 'Max Users',
                    'fr' => 'Utilisateurs max',
                    'es' => 'Usuarios máximos',
                ],
                'slug' => Str::slug('max users'),
                'group' => FeatureGroupEnum::LIMIT->value,
                'is_active' => ActivationStatusEnum::ACTIVE->value,
            ],
            [
                'name' => [
                    'ar' => 'الحد الأقصى للتخزين',
                    'en' => 'Max Storage',
                    'fr' => 'Stockage max',
                    'es' => 'Almacenamiento máximo',
                ],
                'slug' => Str::slug('max storage'),
                'group' => FeatureGroupEnum::LIMIT->value,
                'is_active' => ActivationStatusEnum::ACTIVE->value,
            ],
            [
                'name' => [
                    'ar' => 'الحد الأقصى لجهات الاتصال',
                    'en' => 'Max Contacts',
                    'fr' => 'Contacts max',
                    'es' => 'Contactos máximos',
                ],
                'slug' => Str::slug('max contacts'),
                'group' => FeatureGroupEnum::LIMIT->value,
                'is_active' => ActivationStatusEnum::ACTIVE->value,
            ],
            [
                'name' => [
                    'ar' => 'حد الائتمان',
                    'en' => 'Credit Limit',
                    'fr' => 'Limite de crédit',
                    'es' => 'Límite de crédito',
                ],
                'slug' => Str::slug('credit limit'),
                'group' => FeatureGroupEnum::LIMIT->value,
                'is_active' => ActivationStatusEnum::ACTIVE->value,
            ],
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }

    }
}
