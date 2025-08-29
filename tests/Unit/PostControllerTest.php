<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_posts_index()
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
    }

    public function test_can_view_single_post()
    {
        $post = Post::factory()->create();

        $response = $this->get("/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertViewIs('posts.show');
        $response->assertViewHas('post', $post);
    }

    public function test_can_view_create_post_form()
    {
        $response = $this->get('/posts/create');

        $response->assertStatus(200);
        $response->assertViewIs('posts.create');
    }

    public function test_can_create_post()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ];

        $response = $this->post('/posts', $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
        ]);
    }

    public function test_cannot_create_post_without_required_fields()
    {
        $response = $this->post('/posts', []);

        $response->assertSessionHasErrors(['title', 'content']);
    }

    public function test_authenticated_user_can_edit_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/posts/{$post->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('posts.edit');
    }

    public function test_authenticated_user_cannot_edit_others_post()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get("/posts/{$post->id}/edit");

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated content.',
            'category_id' => $category->id, // Добавляем category_id
        ];

        $response = $this->actingAs($user)->put("/posts/{$post->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post',
            'content' => 'Updated content.',
        ]);
    }

    public function test_authenticated_user_can_delete_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/posts/{$post->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}