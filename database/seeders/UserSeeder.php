<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::create([
            'name'     => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // Akun Kasir
        User::create([
            'name'     => 'Kasir Saung',
            'username' => 'kasir',
            'password' => Hash::make('password123'),
            'role'     => 'kasir',
        ]);

        // Akun Koki / Dapur
        User::create([
            'name'     => 'Koki Dapur',
            'username' => 'koki',
            'password' => Hash::make('password123'),
            'role'     => 'koki',
        ]);
    }
}