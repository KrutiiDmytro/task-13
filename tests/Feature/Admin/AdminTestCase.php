<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем роли для Spatie Permission
        $this->createRoles();

        $this->admin = User::factory()->create(['admin' => true]);
        $this->regularUser = User::factory()->create(['admin' => false]);
    }

    protected function createRoles(): void
    {
        // Создаем базовые роли
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'user']);

        // Создаем базовые права (если нужно)
        Permission::create(['name' => 'manage posts']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage categories']);
    }

    protected function actingAsAdmin()
    {
        return $this->actingAs($this->admin);
    }

    protected function actingAsRegularUser()
    {
        return $this->actingAs($this->regularUser);
    }
}
