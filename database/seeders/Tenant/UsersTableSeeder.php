<?php

namespace Database\Seeders\Tenant;

use App\Models\Landlord\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            \App\Models\Tenant\User::create([
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'remember_token' => $user->remember_token,
                'landlord_user_id' => $user->id,
            ]);
        }
    }
}
