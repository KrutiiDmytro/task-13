<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_comment()
    {
        $post = Post::factory()->create();

        $comment = Comment::create([
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'content' => 'This is a test comment.',
            'post_id' => $post->id,
        ]);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test Author', $comment->author_name);
        $this->assertEquals('test@example.com', $comment->author_email);
        $this->assertEquals('This is a test comment.', $comment->content);
        $this->assertEquals($post->id, $comment->post_id);
    }

    public function test_comment_belongs_to_post()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
    }

    public function test_comment_validation_rules()
    {
        $comment = new Comment;

        $this->assertContains('author_name', $comment->getFillable());
        $this->assertContains('author_email', $comment->getFillable());
        $this->assertContains('content', $comment->getFillable());
        $this->assertContains('post_id', $comment->getFillable());
    }
}
