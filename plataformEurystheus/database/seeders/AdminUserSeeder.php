<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar se já existe um admin
        if (User::where('role', UserRole::ADMIN)->exists()) {
            return;
        }

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@eurystheus.com',
            'password' => Hash::make('admin123'),
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);
    }
}
