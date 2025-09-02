<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostOwnerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_owner_can_edit_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('posts.edit', $post));

        $response->assertStatus(200);
    }

    public function test_admin_can_edit_any_post()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get(route('posts.edit', $post));

        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_edit_post()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['is_admin' => false]);
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('posts.edit', $post));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_edit_post()
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.edit', $post));

        $response->assertRedirect(route('login'));
    }

    public function test_post_owner_can_delete_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_admin_can_delete_any_post()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}