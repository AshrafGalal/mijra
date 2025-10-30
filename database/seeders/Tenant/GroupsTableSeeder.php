<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Group;
use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Group::factory()->count(10)->create();
    }
}
