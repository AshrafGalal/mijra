<?php

namespace Database\Seeders\Landlord;

use App\Models\Landlord\Tenant;
use App\Models\Landlord\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $tenant->update(['owner_id' => User::query()->where('tenant_id', $tenant->id)->inRandomOrder()->first()->id]);
        }
    }
}
