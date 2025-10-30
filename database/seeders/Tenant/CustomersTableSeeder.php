<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Customer;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Customer::factory()->count(30)->create();
    }
}
