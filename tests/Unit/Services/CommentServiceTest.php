<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CommentService;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommentService();
    }

    public function test_list_returns_paginated_comments_with_post_loaded(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $paginator = $this->service->list(new Request());

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertCount(3, $paginator->items());
        $first = $paginator->items()[0];
        $this->assertInstanceOf(Comment::class, $first);
        $this->assertTrue($first->relationLoaded('post'));
    }

    public function test_create_maps_author_to_author_name_and_persists(): void
    {
        $post = Post::factory()->create();

        $comment = $this->service->create([
            'author'       => 'Test Author',
            'author_email' => 'test@example.com',
            'content'      => 'Hello world',
            'post_id'      => $post->id,
        ]);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertDatabaseHas('comments', [
            'id'           => $comment->id,
            'author_name'  => 'Test Author',
            'author_email' => 'test@example.com',
            'content'      => 'Hello world',
            'post_id'      => $post->id,
        ]);
    }

    public function test_update_changes_fields(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $updated = $this->service->update($comment, [
            'author'       => 'Updated Author',
            'author_email' => 'updated@example.com',
            'content'      => 'Updated content',
        ]);

        $this->assertSame('Updated Author', $updated->author_name);
        $this->assertSame('updated@example.com', $updated->author_email);
        $this->assertSame('Updated content', $updated->content);

        $this->assertDatabaseHas('comments', [
            'id'           => $comment->id,
            'author_name'  => 'Updated Author',
            'author_email' => 'updated@example.com',
            'content'      => 'Updated content',
        ]);
    }

    public function test_delete_removes_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->service->delete($comment);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}