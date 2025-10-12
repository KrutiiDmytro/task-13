<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function index_displays_comments(): void
    {
        $comments = Comment::factory()->count(3)->create();

        $response = $this->get(route('comments.index'));

        $response->assertStatus(200)
            ->assertViewIs('comments.index')
            ->assertViewHas('comments');

        $viewComments = $response->viewData('comments');
        $this->assertCount(3, $viewComments->items());
    }

    #[Test]
    public function create_displays_form(): void
    {
        $posts = Post::factory()->count(2)->create();

        $response = $this->get(route('comments.create'));

        $response->assertStatus(200)
            ->assertViewIs('comments.create')
            ->assertViewHas('posts');

        $viewPosts = $response->viewData('posts');
        $this->assertCount(2, $viewPosts);
    }

    #[Test]
    public function store_creates_comment_and_redirects(): void
    {
        $post = Post::factory()->create();

        $commentData = [
            'author' => 'Test Author',
            'content' => 'This is a test comment',
            'post_id' => $post->id,
        ];

        $response = $this->post(route('comments.store'), $commentData);

        $response->assertRedirect(route('posts.show', $post->id));

        $this->assertDatabaseHas('comments', [
            'author_name' => 'Test Author',
            'content' => 'This is a test comment',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->post(route('comments.store'), []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
        $this->assertEquals(0, Comment::count());
    }

    #[Test]
    public function store_validates_post_exists(): void
    {
        $response = $this->post(route('comments.store'), [
            'author' => 'Test Author',
            'content' => 'Test content',
            'post_id' => 99999, // Несуществующий post_id
        ]);

        $response->assertSessionHasErrors(['post_id']);
        $this->assertEquals(0, Comment::count());
    }

    #[Test]
    public function show_displays_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->get(route('comments.show', $comment));

        $response->assertStatus(200)
            ->assertViewIs('comments.show')
            ->assertViewHas('comment');

        $viewComment = $response->viewData('comment');
        $this->assertTrue($viewComment->is($comment));
        $this->assertTrue($viewComment->relationLoaded('post'));
    }

    #[Test]
    public function edit_displays_form(): void
    {
        $comment = Comment::factory()->create();
        $posts = Post::factory()->count(2)->create();

        $response = $this->get(route('comments.edit', $comment));

        $response->assertStatus(200)
            ->assertViewIs('comments.edit')
            ->assertViewHasAll(['comment', 'posts']);

        $viewComment = $response->viewData('comment');
        $this->assertTrue($viewComment->is($comment));
    }

    #[Test]
    public function update_modifies_comment_and_redirects(): void
    {
        $comment = Comment::factory()->create([
            'author_name' => 'Original Author',
            'content' => 'Original content',
        ]);

        $newPost = Post::factory()->create();

        $updateData = [
            'author' => 'Updated Author',
            'content' => 'Updated content',
            'post_id' => $newPost->id,
        ];

        $response = $this->put(route('comments.update', $comment), $updateData);

        $response->assertRedirect(route('comments.show', $comment));

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'author_name' => 'Updated Author',
            'content' => 'Updated content',
            'post_id' => $newPost->id,
        ]);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->put(route('comments.update', $comment), []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
    }

    #[Test]
    public function destroy_deletes_comment_and_redirects(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->delete(route('comments.destroy', $comment));

        $response->assertRedirect(route('comments.index'));
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function routes_exist_for_all_comment_actions(): void
    {
        $comment = Comment::factory()->create();

        // Проверяем, что все роуты существуют
        $this->assertTrue(route('comments.index') !== null);
        $this->assertTrue(route('comments.create') !== null);
        $this->assertTrue(route('comments.store') !== null);
        $this->assertTrue(route('comments.show', $comment) !== null);
        $this->assertTrue(route('comments.edit', $comment) !== null);
        $this->assertTrue(route('comments.update', $comment) !== null);
        $this->assertTrue(route('comments.destroy', $comment) !== null);
    }
}
