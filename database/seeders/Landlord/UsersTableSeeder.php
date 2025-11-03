<?php

namespace Database\Seeders\Landlord;

use App\Models\Landlord\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::factory()->create(['email' => 'eslam@gmail.com']);
        $user2 = User::factory()->create(['email' => 'eslam2@gmail.com']);
    }
}
