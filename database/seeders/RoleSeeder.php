<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаём права (только если их нет)
        $permissions = [
            'manage-posts',
            'manage-categories',
            'manage-comments',
            'manage-tags',
            'manage-users',
            'view-admin-panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Создаём роли (только если их нет)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $authorRole = Role::firstOrCreate(['name' => 'author']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Назначаем права ролям (только если они ещё не назначены)
        if (! $adminRole->hasPermissionTo('manage-posts')) {
            $adminRole->givePermissionTo(Permission::all()); // Админ получает все права
        }

        if (! $authorRole->hasPermissionTo('manage-posts')) {
            $authorRole->givePermissionTo(['manage-posts', 'manage-categories', 'manage-tags']); // Автор может управлять контентом
        }

        // Создаём тестового админа, если его нет
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => bcrypt('password123'),
            ]
        );

        // Назначаем роль админа, если её нет
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('Роли и права успешно созданы или обновлены!');
        $this->command->info('Тестовый админ: admin@example.com / password123');
    }
}
