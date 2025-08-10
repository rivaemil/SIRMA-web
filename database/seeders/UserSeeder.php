<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        // Mecánico
        User::create([
            'name' => 'Juan Mecánico',
            'email' => 'mechanic@example.com',
            'password' => Hash::make('mechanic123'),
            'role' => 'mechanic'
        ]);

        // Cliente
        User::create([
            'name' => 'Carlos Cliente',
            'email' => 'client@example.com',
            'password' => Hash::make('client123'),
            'role' => 'client'
        ]);
    }
}

