<?php

namespace Database\Seeders;

use Database\Seeders\Tenant\PermissionsTableSeeder;
use Database\Seeders\Tenant\RolesTableSeeder;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
    }
}
