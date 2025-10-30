<?php

namespace Database\Seeders\Landlord;

use App\Enum\LandlordPermissionsEnum;
use App\Models\Landlord\Permission;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (LandlordPermissionsEnum::cases() as $permission) {
            Permission::query()->updateOrCreate([
                'name' => $permission->value,
                'group' => $permission->getGroup(),
                'guard_name' => 'landlord',
            ]);
        }
    }
}
