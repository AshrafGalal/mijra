<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['is_active' => true, 'description' => 'Full access to all features']
        );
    }
}
