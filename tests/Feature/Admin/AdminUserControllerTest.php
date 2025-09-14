<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем роли
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
        
        // Создаем админа
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        // Создаем обычного пользователя
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    #[Test]
    public function admin_can_view_users_index()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    #[Test]
    public function users_index_shows_pagination()
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->hasPages());
    }

    #[Test]
    public function users_are_ordered_by_latest()
    {
        // Создаем пользователей с разными датами создания
        $firstUser = User::factory()->create(['created_at' => now()->subDay()]);
        $secondUser = User::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $users = $response->viewData('users');
        
        // Находим наших пользователей в списке
        $secondUserInList = $users->firstWhere('id', $secondUser->id);
        $firstUserInList = $users->firstWhere('id', $firstUser->id);
        
        // Проверяем, что второй пользователь идет раньше первого
        $this->assertNotNull($secondUserInList);
        $this->assertNotNull($firstUserInList);
        
        // Проверяем порядок по индексу в коллекции
        $secondUserIndex = $users->search(function($user) use ($secondUser) {
            return $user->id === $secondUser->id;
        });
        $firstUserIndex = $users->search(function($user) use ($firstUser) {
            return $user->id === $firstUser->id;
        });
        
        $this->assertLessThan($firstUserIndex, $secondUserIndex, 
            'Новый пользователь должен идти раньше старого в списке');
    }

    #[Test]
    public function users_index_loads_relationships()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertTrue($users->first()->relationLoaded('roles'));
    }

    #[Test]
    public function admin_can_view_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
        $response->assertViewHas('user', $user);
    }

    #[Test]
    public function user_show_loads_relationships()
    {
        $user = User::factory()->create();
        $user->posts()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $user = $response->viewData('user');
        $this->assertTrue($user->relationLoaded('roles'));
        $this->assertTrue($user->relationLoaded('posts'));
    }

    #[Test]
    public function admin_can_view_create_user_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
        $response->assertViewHas('roles');
    }

    #[Test]
    public function create_form_shows_roles()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $roles = $response->viewData('roles');
        $this->assertCount(2, $roles); // admin и user
    }

    #[Test]
    public function admin_can_create_user()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['user'],
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Пользователь успешно создан!');
        
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function create_user_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function create_user_validates_email_format()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function create_user_validates_unique_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function create_user_validates_password_confirmation()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function create_user_validates_password_minimum_length()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '123',
                'password_confirmation' => '123',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function create_user_validates_roles_exist()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['nonexistent-role'],
            ]);

        $response->assertSessionHasErrors(['roles.0']);
    }

    #[Test]
    public function create_user_without_roles()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function admin_can_view_edit_user_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user', $user);
        $response->assertViewHas('roles');
    }

    #[Test]
    public function admin_can_update_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'roles' => ['admin'],
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        $response->assertSessionHas('success', 'Пользователь успешно обновлён!');
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    #[Test]
    public function update_user_validates_required_fields()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    #[Test]
    public function update_user_validates_unique_email()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user1), [
                'name' => 'Updated Name',
                'email' => 'user2@example.com',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function update_user_allows_same_email()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'test@example.com',
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function update_user_with_password()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        
        $user->refresh();
        $this->assertTrue(password_verify('newpassword123', $user->password));
    }

    #[Test]
    public function update_user_without_password()
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        
        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    #[Test]
    public function update_user_syncs_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'roles' => ['admin'],
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        
        $user->refresh();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }

    #[Test]
    public function update_user_removes_all_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'roles' => [],
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        
        $user->refresh();
        $this->assertFalse($user->hasRole('user'));
    }

    #[Test]
    public function admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Пользователь успешно удалён!');
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    #[Test]
    public function cannot_delete_own_account()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Нельзя удалить свой собственный аккаунт!');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    #[Test]
    public function cannot_delete_user_with_posts()
    {
        $user = User::factory()->create();
        $user->posts()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Нельзя удалить пользователя, у которого есть посты!');
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    #[Test]
    public function guest_cannot_access_users()
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_users()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_view_empty_users_list()
    {
        // Удаляем всех пользователей кроме админа
        User::where('id', '!=', $this->admin->id)->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    #[Test]
    public function user_show_displays_posts_count()
    {
        $user = User::factory()->create();
        $user->posts()->create([
            'title' => 'Test Post 1',
            'content' => 'Test content 1',
            'category_id' => Category::factory()->create()->id,
        ]);
        $user->posts()->create([
            'title' => 'Test Post 2',
            'content' => 'Test content 2',
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $user = $response->viewData('user');
        $this->assertEquals(2, $user->posts->count());
    }

    #[Test]
    public function create_user_with_multiple_roles()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['admin', 'user'],
            ]);

        $response->assertRedirect(route('admin.users.index'));
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
    }

    #[Test]
    public function update_user_with_multiple_roles()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'roles' => ['admin', 'user'],
            ]);

        $response->assertRedirect(route('admin.users.show', $user));
        
        $user->refresh();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
    }
}