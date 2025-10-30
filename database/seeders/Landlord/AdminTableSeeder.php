<?php

namespace Database\Seeders\Landlord;

use App\Models\Landlord\Admin;
use Illuminate\Database\Seeder;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::factory()->create(['email' => 'superadmin@localhost']);
        Admin::factory()->count(3)->create();
    }
}
