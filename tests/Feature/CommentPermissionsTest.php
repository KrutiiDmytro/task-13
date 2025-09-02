<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_edit_own_comment()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author' => 'Test User'
        ]);

        $response = $this->actingAs($user)->get(route('comments.edit', $comment));

        $response->assertStatus(200);
    }

    public function test_admin_can_edit_any_comment()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author' => 'Someone Else'
        ]);

        $response = $this->actingAs($admin)->get(route('comments.edit', $comment));

        $response->assertStatus(200);
    }

    public function test_user_cannot_edit_others_comment()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author' => 'Someone Else'
        ]);

        $response = $this->actingAs($user)->get(route('comments.edit', $comment));

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_comment()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author' => 'Someone Else'
        ]);

        $response = $this->actingAs($admin)->delete(route('comments.destroy', $comment));

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}