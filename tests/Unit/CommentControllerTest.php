<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_comments_index()
    {
        $response = $this->get('/comments');

        $response->assertStatus(200);
        $response->assertViewIs('comments.index');
    }

    public function test_can_create_comment()
    {
        $post = Post::factory()->create();

        $commentData = [
            'author' => 'Test Commenter',
            'content' => 'This is a test comment.',
            'post_id' => $post->id,
        ];

        $response = $this->post('/comments', $commentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Commenter',
            'content' => 'This is a test comment.',
            'post_id' => $post->id,
        ]);
    }

    public function test_cannot_create_comment_without_required_fields()
    {
        $response = $this->post('/comments', []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
    }
}