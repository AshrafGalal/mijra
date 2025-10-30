<?php

namespace Database\Seeders\Tenant;

use App\Enum\PermissionsEnum;
use App\Models\Tenant\Permission;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionsEnum::cases() as $permission) {
            Permission::query()->updateOrCreate([
                'name' => $permission->value,
                'group' => $permission->getGroup(),
            ]);
        }
    }
}
