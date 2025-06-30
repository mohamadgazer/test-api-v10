<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 🟢 أدمن رئيسي
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => 'super_admin',
            ]
        );

        // 🟡 مدير عادي
        User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => 'manager',
            ]
        );

        // 🔵 أدمن للعرض فقط
        User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Viewer',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => 'viewer',
            ]
        );

        // 👤 زبون عادي
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'role' => "customer", // أو ممكن نخليها 'customer' لو تحب تبني عليه لاحقًا
            ]
        );
    }
}
