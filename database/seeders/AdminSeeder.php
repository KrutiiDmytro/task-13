<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Создать роль admin
        Role::firstOrCreate(['name' => 'admin']);

        // Создать администратора
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
            ]
        );

        $admin->assignRole('admin');
    }
}