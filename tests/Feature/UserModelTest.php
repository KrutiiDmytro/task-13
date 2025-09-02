<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_false_for_regular_user()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_returns_true_for_user_with_admin_role()
    {
        $user = User::factory()->create(['is_admin' => false]);
        
        // Создаем роль admin
        $adminRole = Role::create(['name' => 'admin']);
        $user->assignRole('admin');

        $this->assertTrue($user->isAdmin());
    }

    public function test_can_edit_post_returns_true_for_post_owner()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->canEditPost($post));
    }

    public function test_can_edit_post_returns_true_for_admin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($admin->canEditPost($post));
    }

    public function test_can_edit_post_returns_false_for_non_owner_non_admin()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['is_admin' => false]);
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($otherUser->canEditPost($post));
    }
}