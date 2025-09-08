<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principale
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('PasswordForte!123'),
                'role' => 'admin',
                'is_active' => true,
                'avatar' => 'users/avatars/admin.png',
            ]
        );

        // Editor demo
        User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Content Editor',
                'password' => Hash::make('EditorPass!123'),
                'role' => 'editor',
                'is_active' => true,
                'avatar' => null,
            ]
        );

        // Utente base demo
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Basic User',
                'password' => Hash::make('UserPass!123'),
                'role' => 'user',
                'is_active' => true,
                'avatar' => null,
            ]
        );

        $this->command->info('Utenti demo creati: admin, editor, user');
    }
}
