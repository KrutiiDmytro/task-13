<?php

namespace Tests\Unit\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_comment(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $comment = Comment::create([
            'content' => 'This is a test comment',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('This is a test comment', $comment->content);
        $this->assertEquals('Test Author', $comment->author_name);
        $this->assertEquals('test@example.com', $comment->author_email);
        $this->assertEquals($post->id, $comment->post_id);
        $this->assertEquals($user->id, $comment->user_id);
    }

    #[Test]
    public function post_relationship_method_returns_belongs_to(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        // ЯВНО вызываем метод post() для покрытия
        $postRelation = $comment->post();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $postRelation);
        $this->assertEquals('App\Models\Post', $postRelation->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_post(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertTrue($comment->post->is($post));
    }

    #[Test]
    public function user_relationship_method_returns_belongs_to(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        // ЯВНО вызываем метод user() для покрытия
        $userRelation = $comment->user();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertEquals('App\Models\User', $userRelation->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertTrue($comment->user->is($user));
    }

    #[Test]
    public function it_can_create_comment_without_user(): void
    {
        $post = Post::factory()->create();

        $comment = Comment::create([
            'content' => 'Anonymous comment',
            'author_name' => 'Anonymous',
            'author_email' => 'anon@example.com',
            'post_id' => $post->id,
            'user_id' => null,
        ]);

        $this->assertNull($comment->user_id);
        $this->assertNull($comment->user);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $comment = new Comment;

        $expectedFillable = [
            'content', 'author_name', 'author_email', 'post_id', 'user_id',
        ];

        $this->assertEquals($expectedFillable, $comment->getFillable());
    }
}
