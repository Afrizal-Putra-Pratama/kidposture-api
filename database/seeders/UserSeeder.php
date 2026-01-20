<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin/Parent Test User
        User::create([
            'name' => 'Admin Parent',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'is_premium' => false,
        ]);

        // Fisioterapis Test User
        User::create([
            'name' => 'Fisioterapis Test',
            'email' => 'fisio@example.com',
            'password' => Hash::make('password'),
            'role' => 'physiotherapist',
            'is_premium' => false,
        ]);
    }
}
