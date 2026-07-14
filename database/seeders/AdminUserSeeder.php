<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::updateOrCreate(
            ['teacher_id' => 'ADMIN-001'],
            [
                'email' => env('INITIAL_ADMIN_EMAIL', 'admin@example.com'),
                'name' => 'Admin: JJ',
                'role' => 'admin',
                'is_active' => true,
                'password' => bcrypt(env('INITIAL_ADMIN_PASSWORD', 'default_secure_password')),
                'requires_password_change' => false,
            ]
        );

        // Test borrower account
        User::updateOrCreate(
            ['teacher_id' => 'BORROWER-001'],
            [
                'email' => 'borrower@bces.edu.ph',
                'name' => 'Test Borrower',
                'role' => 'borrower',
                'is_active' => true,
                'password' => bcrypt('Borrower@123'),
                'requires_password_change' => false,
            ]
        );
    }
}