<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Stage;
use Illuminate\Database\Seeder;

class StagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stage::factory()->count(10)->create();
    }
}
