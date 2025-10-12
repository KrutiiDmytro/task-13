<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;

class UserControllerTest extends AdminTestCase
{
    public function test_admin_can_list_users()
    {
        User::factory()->count(3)->create();

        $this->actingAsAdmin()
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_admin_can_view_create_user_form()
    {
        $this->actingAsAdmin()
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertViewIs('admin.users.create')
            ->assertViewHas('roles');
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['user'],
        ];

        $this->actingAsAdmin()
            ->post(route('admin.users.store'), $userData)
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_admin_can_show_user()
    {
        $user = User::factory()->create();

        $this->actingAsAdmin()
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user');
    }

    public function test_admin_can_edit_user()
    {
        $user = User::factory()->create();

        $this->actingAsAdmin()
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertViewIs('admin.users.edit')
            ->assertViewHas(['user', 'roles']);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => ['editor'],
        ];

        $this->actingAsAdmin()
            ->put(route('admin.users.update', $user), $updateData)
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_admin_can_delete_user_without_posts()
    {
        $user = User::factory()->create();

        $this->actingAsAdmin()
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_user_with_posts()
    {
        $user = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id]);

        $this->actingAsAdmin()
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_themselves()
    {
        $this->actingAsAdmin()
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_user_creation_validation()
    {
        $this->actingAsAdmin()
            ->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_regular_user_cannot_access_users()
    {
        $this->actingAsRegularUser()
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_update_user_with_password()
    {
        $user = User::factory()->create();
        $originalPasswordHash = $user->password;

        $updateData = [
            'name' => 'Updated Name with Password',
            'email' => 'updated-with-password@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'roles' => ['user'],
        ];

        $this->actingAsAdmin()
            ->put(route('admin.users.update', $user), $updateData)
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $user->refresh();

        // Проверяем, что пароль изменился (покрывает строки 82-84)
        $this->assertNotEquals($originalPasswordHash, $user->password);
        $this->assertTrue(\Hash::check('newpassword123', $user->password));

        // Проверяем остальные данные
        $this->assertEquals('Updated Name with Password', $user->name);
        $this->assertEquals('updated-with-password@example.com', $user->email);
    }

    public function test_admin_can_update_user_without_password()
    {
        $user = User::factory()->create();
        $originalPasswordHash = $user->password;

        $updateData = [
            'name' => 'Updated Name without Password',
            'email' => 'updated-without-password@example.com',
            // Не передаем password
            'roles' => ['editor'],
        ];

        $this->actingAsAdmin()
            ->put(route('admin.users.update', $user), $updateData)
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $user->refresh();

        // Проверяем, что пароль НЕ изменился (условие на строке 82 = false)
        $this->assertEquals($originalPasswordHash, $user->password);

        // Проверяем, что остальные данные обновились
        $this->assertEquals('Updated Name without Password', $user->name);
        $this->assertEquals('updated-without-password@example.com', $user->email);
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_admin_can_update_user_without_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // Назначаем роль

        $updateData = [
            'name' => 'Updated Name without Roles',
            'email' => 'updated-no-roles@example.com',
            // Не передаем roles вообще
        ];

        $this->actingAsAdmin()
            ->put(route('admin.users.update', $user), $updateData)
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $user->refresh();

        // Проверяем, что все роли удалены (покрывает строки 89-91)
        $this->assertFalse($user->hasAnyRole(['user', 'editor', 'admin']));

        // Проверяем, что остальные данные обновились
        $this->assertEquals('Updated Name without Roles', $user->name);
        $this->assertEquals('updated-no-roles@example.com', $user->email);
    }

    public function test_admin_can_update_user_with_empty_roles_array()
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // Назначаем роль

        $updateData = [
            'name' => 'Updated Name with Empty Roles',
            'email' => 'updated-empty-roles@example.com',
            'roles' => [], // Передаем пустой массив ролей
        ];

        $this->actingAsAdmin()
            ->put(route('admin.users.update', $user), $updateData)
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $user->refresh();

        // Проверяем, что все роли удалены (покрывает строку 88)
        $this->assertFalse($user->hasAnyRole(['user', 'editor', 'admin']));

        // Проверяем, что остальные данные обновились
        $this->assertEquals('Updated Name with Empty Roles', $user->name);
        $this->assertEquals('updated-empty-roles@example.com', $user->email);
    }
}
