<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Department;
use Illuminate\Database\Seeder;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Department::factory()->count(10)->create();
    }
}
