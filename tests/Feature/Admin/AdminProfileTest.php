<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_profile_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->get('/admin/profile');

        $response->assertOk();
        $response->assertViewIs('admin.profile.edit');
        $response->assertViewHas('user', $admin);
    }

    public function test_regular_user_cannot_access_admin_profile(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/profile');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_profile(): void
    {
        $response = $this->get('/admin/profile');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_update_profile_information(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile', [
                'name' => 'Updated Admin Name',
                'email' => 'updated@admin.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success', 'Профиль успешно обновлен!');

        $admin->refresh();

        $this->assertSame('Updated Admin Name', $admin->name);
        $this->assertSame('updated@admin.com', $admin->email);
    }

    public function test_admin_profile_update_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile', [
                'name' => '',
                'email' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_admin_profile_update_validates_unique_email(): void
    {
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile', [
                'name' => 'Admin Name',
                'email' => 'taken@example.com',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_update_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('current-password')
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile/password', [
                'current_password' => 'current-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('password_success', 'Пароль успешно изменен!');

        $admin->refresh();
        $this->assertTrue(Hash::check('new-password', $admin->password));
    }

    public function test_admin_password_update_validates_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('current-password')
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_admin_password_update_requires_confirmation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_password_update_validates_minimum_length(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile/password', [
                'current_password' => 'password',
                'password' => '123',
                'password_confirmation' => '123',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_can_update_email_to_same_email(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile', [
                'name' => 'Updated Name',
                'email' => 'admin@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.edit'));

        $admin->refresh();
        $this->assertSame('Updated Name', $admin->name);
        $this->assertSame('admin@example.com', $admin->email);
    }

    public function test_admin_profile_update_validates_email_format(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile', [
                'name' => 'Admin Name',
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_profile_update_validates_name_length(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile', [
                'name' => str_repeat('a', 256),
                'email' => 'admin@example.com',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_password_update_validates_password_confirmation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->patch('/admin/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrors(['password']);
    }
}