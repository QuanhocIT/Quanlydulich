<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@quanlydulich.vn'],
            [
                'name' => 'Administrator',
                'email' => 'admin@quanlydulich.vn',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
