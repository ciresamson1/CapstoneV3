<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Project Manager',
            'email' => 'pm@test.com',
            'password' => Hash::make('password'),
            'role' => 'pm',
        ]);

        User::create([
            'name' => 'Digital Marketer',
            'email' => 'dm@test.com',
            'password' => Hash::make('password'),
            'role' => 'dm',
        ]);

        User::create([
            'name' => 'Client User',
            'email' => 'client@test.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);
    }
}