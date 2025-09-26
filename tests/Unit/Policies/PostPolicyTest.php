<?php

namespace Tests\Unit\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PostPolicy();
    }

    #[Test]
    public function view_any_allows_all_users(): void
    {
        $user = User::factory()->create();
        $guest = null;

        $this->assertTrue($this->policy->viewAny($user));
        $this->assertTrue($this->policy->viewAny($guest));
    }

    #[Test]
    public function view_allows_all_users(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $guest = null;

        $this->assertTrue($this->policy->view($user, $post));
        $this->assertTrue($this->policy->view($guest, $post));
    }

    #[Test]
    public function create_allows_authenticated_users_only(): void
    {
        $user = User::factory()->create();
        $guest = null;

        $this->assertTrue($this->policy->create($user));
        $this->assertFalse($this->policy->create($guest));
    }

    #[Test]
    public function update_denies_guests(): void
    {
        $post = Post::factory()->create();
        $guest = null;

        $this->assertFalse($this->policy->update($guest, $post));
    }

    #[Test]
    public function update_allows_admin_for_any_post(): void
    {
        $admin = User::factory()->create(['admin' => true]);
        $otherUserPost = Post::factory()->create();

        $this->assertTrue($this->policy->update($admin, $otherUserPost));
    }

    #[Test]
    public function update_allows_owner_for_own_post(): void
    {
        $user = User::factory()->create(['admin' => false]);
        $ownPost = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $ownPost));
    }

    #[Test]
    public function update_denies_non_owner_non_admin(): void
    {
        $user = User::factory()->create(['admin' => false]);
        $otherUserPost = Post::factory()->create();

        $this->assertFalse($this->policy->update($user, $otherUserPost));
    }

    #[Test]
    public function delete_denies_guests(): void
    {
        $post = Post::factory()->create();
        $guest = null;

        $this->assertFalse($this->policy->delete($guest, $post));
    }

    #[Test]
    public function delete_allows_admin_for_any_post(): void
    {
        $admin = User::factory()->create(['admin' => true]);
        $otherUserPost = Post::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $otherUserPost));
    }

    #[Test]
    public function delete_allows_owner_for_own_post(): void
    {
        $user = User::factory()->create(['admin' => false]);
        $ownPost = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $ownPost));
    }

    #[Test]
    public function delete_denies_non_owner_non_admin(): void
    {
        $user = User::factory()->create(['admin' => false]);
        $otherUserPost = Post::factory()->create();

        $this->assertFalse($this->policy->delete($user, $otherUserPost));
    }
}