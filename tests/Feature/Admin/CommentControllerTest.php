<?php

namespace Tests\Feature\Admin;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class CommentControllerTest extends AdminTestCase
{
    use WithFaker;

    #[Test]
    public function admin_can_view_index(): void
    {
        Comment::factory()->count(3)->create();

        $response = $this->actingAsAdmin()->get('/admin/comments');

        $response->assertStatus(200)->assertViewIs('admin.comments.index');
        $response->assertViewHas('comments');
    }

    #[Test]
    public function regular_user_forbidden_on_index(): void
    {
        $this->actingAsRegularUser()
            ->get('/admin/comments')
            ->assertStatus(403);
    }

    #[Test]
    public function guest_redirected_to_login_on_index(): void
    {
        auth()->logout();

        $this->get('/admin/comments')
            ->assertRedirect('/login');
    }

    #[Test]
    public function admin_can_view_create(): void
    {
        $response = $this->actingAsAdmin()->get('/admin/comments/create');

        $response->assertStatus(200)->assertViewIs('admin.comments.create');
        $response->assertViewHas('posts');
    }

    #[Test]
    public function admin_can_store_comment(): void
    {
        $post = Post::factory()->create();

        $payload = [
            'author'  => 'Admin Author',
            'content' => 'Admin comment content',
            'post_id' => $post->id,
        ];

        $response = $this->actingAsAdmin()->post('/admin/comments', $payload);

        $response->assertRedirect(route('admin.comments.index'))
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', [
            'author_name' => 'Admin Author',
            'content'     => 'Admin comment content',
            'post_id'     => $post->id,
        ]);
    }

    #[Test]
    public function store_fails_validation_with_invalid_payload(): void
    {
        $this->actingAsAdmin()
            ->post('/admin/comments', [])
            ->assertSessionHasErrors(['author', 'content', 'post_id']);
    }

    #[Test]
    public function admin_can_show_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->actingAsAdmin()->get("/admin/comments/{$comment->id}");

        $response->assertStatus(200)->assertViewIs('admin.comments.show');
        $response->assertViewHas('comment', function ($c) use ($comment) {
            return $c->id === $comment->id;
        });
    }

    #[Test]
    public function admin_can_view_edit(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->actingAsAdmin()->get("/admin/comments/{$comment->id}/edit");

        $response->assertStatus(200)->assertViewIs('admin.comments.edit');
        $response->assertViewHasAll(['comment', 'posts']);
    }

    #[Test]
    public function admin_can_update_comment(): void
    {
        $post    = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $newPost = Post::factory()->create();

        $payload = [
            'author'  => 'Updated Admin',
            'content' => 'Updated content',
            'post_id' => $newPost->id,
        ];

        $response = $this->actingAsAdmin()->put("/admin/comments/{$comment->id}", $payload);

        $response->assertRedirect(route('admin.comments.index'))
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', [
            'id'          => $comment->id,
            'author_name' => 'Updated Admin',
            'content'     => 'Updated content',
            'post_id'     => $newPost->id,
        ]);
    }

    #[Test]
    public function update_fails_validation_with_invalid_payload(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAsAdmin()
            ->put("/admin/comments/{$comment->id}", [])
            ->assertSessionHasErrors(['author', 'content', 'post_id']);
    }

    #[Test]
    public function admin_can_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->actingAsAdmin()->delete("/admin/comments/{$comment->id}");

        $response->assertRedirect(route('admin.comments.index'))
                 ->assertSessionHas('success');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function regular_user_forbidden_on_mutations(): void
    {
        $post    = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->actingAsRegularUser()->post('/admin/comments', [
            'author'  => 'X',
            'content' => 'Y',
            'post_id' => $post->id,
        ])->assertStatus(403);

        $this->actingAsRegularUser()
            ->put("/admin/comments/{$comment->id}", [
                'author'  => 'X2',
                'content' => 'Y2',
                'post_id' => $post->id,
            ])->assertStatus(403);

        $this->actingAsRegularUser()
            ->delete("/admin/comments/{$comment->id}")
            ->assertStatus(403);
    }
}