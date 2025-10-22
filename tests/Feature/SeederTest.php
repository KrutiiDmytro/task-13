<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function role_seeder_creates_permissions(): void
    {
        $this->seed(RoleSeeder::class);

        $permissions = [
            'manage-posts',
            'manage-categories',
            'manage-comments',
            'manage-tags',
            'manage-users',
            'view-admin-panel',
        ];

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission]);
        }
    }

    #[Test]
    public function role_seeder_creates_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $roles = ['admin', 'author', 'user'];

        foreach ($roles as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role]);
        }
    }

    #[Test]
    public function role_seeder_assigns_permissions_to_admin(): void
    {
        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $this->assertTrue($adminRole->hasPermissionTo('manage-posts'));
        $this->assertTrue($adminRole->hasPermissionTo('manage-categories'));
    }

    #[Test]
    public function role_seeder_assigns_permissions_to_author(): void
    {
        $this->seed(RoleSeeder::class);

        $authorRole = Role::where('name', 'author')->first();
        $this->assertTrue($authorRole->hasPermissionTo('manage-posts'));
        $this->assertTrue($authorRole->hasPermissionTo('manage-categories'));
    }

    #[Test]
    public function role_seeder_creates_admin_user(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('Администратор', $admin->name);
        $this->assertTrue($admin->hasRole('admin'));
    }

    #[Test]
    public function role_seeder_is_idempotent(): void
    {
        $this->seed(RoleSeeder::class);
        $permissionCount = Permission::count();
        $roleCount = Role::count();

        // Запускаем снова
        $this->seed(RoleSeeder::class);

        // Количество не должно увеличиться
        $this->assertEquals($permissionCount, Permission::count());
        $this->assertEquals($roleCount, Role::count());
    }
}
