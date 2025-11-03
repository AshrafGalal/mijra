<?php

namespace Database\Seeders\Landlord;

use App\Enum\ActivationStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'AppSumo',
                'is_active' => ActivationStatusEnum::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Envato',
                'is_active' => ActivationStatusEnum::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        DB::connection('landlord')
            ->table('sources')->insert($sources);
    }
}
