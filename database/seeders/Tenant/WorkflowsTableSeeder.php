<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Workflow;
use Illuminate\Database\Seeder;

class WorkflowsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Workflow::factory()->count(10)->create();
    }
}
