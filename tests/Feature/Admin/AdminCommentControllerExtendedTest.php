<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminCommentControllerExtendedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Post $post;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->category = Category::factory()->create(['name' => 'Test Category']);
        $this->post = Post::factory()->create(['category_id' => $this->category->id]);
    }

    #[Test]
    public function admin_can_view_comments_index_with_pagination()
    {
        // Создаем 20 комментариев
        for ($i = 1; $i <= 20; $i++) {
            Comment::factory()->create([
                'post_id' => $this->post->id,
                'author' => "Author {$i}",
                'content' => "Comment content {$i}"
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200)
                ->assertViewIs('admin.comments.index')
                ->assertViewHas('comments');

        $comments = $response->viewData('comments');
        $this->assertCount(15, $comments); // Пагинация по 15
        $this->assertTrue($comments->hasPages());
    }

    #[Test]
    public function comments_index_includes_post_relationship()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'Test Author',
            'content' => 'Test content'
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200);
        
        $comments = $response->viewData('comments');
        $firstComment = $comments->first();
        
        $this->assertTrue($firstComment->relationLoaded('post'));
        $this->assertEquals($this->post->id, $firstComment->post->id);
    }

    #[Test]
    public function comments_are_sorted_by_latest_in_index()
    {
        $comment1 = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'First Author',
            'created_at' => now()->subDays(2)
        ]);
        
        $comment2 = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'Second Author',
            'created_at' => now()->subDay()
        ]);
        
        $comment3 = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'Third Author',
            'created_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200);
        
        $comments = $response->viewData('comments');
        $commentAuthors = $comments->pluck('author')->toArray();
        
        $this->assertEquals(['Third Author', 'Second Author', 'First Author'], $commentAuthors);
    }

    #[Test]
    public function admin_can_view_create_comment_form()
    {
        $response = $this->actingAs($this->admin)->get('/admin/comments/create');

        $response->assertStatus(200)
                ->assertViewIs('admin.comments.create')
                ->assertViewHas('posts');

        $posts = $response->viewData('posts');
        $this->assertTrue($posts->contains($this->post));
    }

    #[Test]
    public function regular_user_cannot_access_create_comment_form()
    {
        $response = $this->actingAs($this->user)->get('/admin/comments/create');

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_create_comment_form()
    {
        $response = $this->get('/admin/comments/create');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function admin_can_create_comment_with_all_fields()
    {
        $commentData = [
            'author' => 'New Test Author',
            'content' => 'This is a new test comment content',
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $commentData);

        $response->assertRedirect('/admin/comments')
                ->assertSessionHas('success', 'Комментарий успешно создан!');

        $this->assertDatabaseHas('comments', $commentData);
    }

    #[Test]
    public function comment_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function comment_creation_validates_author_max_length()
    {
        $longAuthor = str_repeat('a', 256); // Превышаем лимит в 255 символов

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', [
                             'author' => $longAuthor,
                             'content' => 'Valid content',
                             'post_id' => $this->post->id
                         ]);

        $response->assertSessionHasErrors(['author']);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function comment_creation_validates_content_max_length()
    {
        $longContent = str_repeat('a', 1001); // Превышаем лимит в 1000 символов

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', [
                             'author' => 'Valid Author',
                             'content' => $longContent,
                             'post_id' => $this->post->id
                         ]);

        $response->assertSessionHasErrors(['content']);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function comment_creation_validates_post_exists()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', [
                             'author' => 'Valid Author',
                             'content' => 'Valid content',
                             'post_id' => 999 // Несуществующий пост
                         ]);

        $response->assertSessionHasErrors(['post_id']);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function admin_can_view_comment_details()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'Test Author',
            'content' => 'Test comment content'
        ]);

        $response = $this->actingAs($this->admin)
                         ->get("/admin/comments/{$comment->id}");

        $response->assertStatus(200)
                ->assertViewIs('admin.comments.show')
                ->assertViewHas('comment');

        $viewComment = $response->viewData('comment');
        $this->assertEquals($comment->id, $viewComment->id);
        $this->assertTrue($viewComment->relationLoaded('post'));
    }

    #[Test]
    public function admin_can_view_edit_comment_form()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->actingAs($this->admin)
                         ->get("/admin/comments/{$comment->id}/edit");

        $response->assertStatus(200)
                ->assertViewIs('admin.comments.edit')
                ->assertViewHas(['comment', 'posts']);

        $viewComment = $response->viewData('comment');
        $posts = $response->viewData('posts');
        
        $this->assertEquals($comment->id, $viewComment->id);
        $this->assertTrue($posts->contains($this->post));
    }

    #[Test]
    public function admin_can_update_comment()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'Original Author',
            'content' => 'Original content'
        ]);

        $updateData = [
            'author' => 'Updated Author',
            'content' => 'Updated content',
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->put("/admin/comments/{$comment->id}", $updateData);

        $response->assertRedirect('/admin/comments')
                ->assertSessionHas('success', 'Комментарий успешно обновлён!');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'author' => 'Updated Author',
            'content' => 'Updated content'
        ]);
    }

    #[Test]
    public function admin_can_change_comment_post()
    {
        $anotherPost = Post::factory()->create(['category_id' => $this->category->id]);
        
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'author' => 'Test Author',
            'content' => 'Test content'
        ]);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/comments/{$comment->id}", [
                             'author' => 'Test Author',
                             'content' => 'Test content',
                             'post_id' => $anotherPost->id
                         ]);

        $response->assertRedirect('/admin/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'post_id' => $anotherPost->id
        ]);
    }

    #[Test]
    public function comment_update_validates_required_fields()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/comments/{$comment->id}", []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
    }

    #[Test]
    public function comment_update_validates_post_exists()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/comments/{$comment->id}", [
                             'author' => 'Valid Author',
                             'content' => 'Valid content',
                             'post_id' => 999 // Несуществующий пост
                         ]);

        $response->assertSessionHasErrors(['post_id']);
    }

    #[Test]
    public function admin_can_delete_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->actingAs($this->admin)
                         ->delete("/admin/comments/{$comment->id}");

        $response->assertRedirect('/admin/comments')
                ->assertSessionHas('success', 'Комментарий успешно удалён!');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function returns_404_when_accessing_non_existent_comment()
    {
        $response = $this->actingAs($this->admin)->get('/admin/comments/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_editing_non_existent_comment()
    {
        $response = $this->actingAs($this->admin)->get('/admin/comments/999/edit');

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_updating_non_existent_comment()
    {
        $response = $this->actingAs($this->admin)
                         ->put('/admin/comments/999', [
                             'author' => 'Test Author',
                             'content' => 'Test content',
                             'post_id' => $this->post->id
                         ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_deleting_non_existent_comment()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/comments/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function regular_user_cannot_create_comment()
    {
        $response = $this->actingAs($this->user)
                         ->post('/admin/comments', [
                             'author' => 'Test Author',
                             'content' => 'Test content',
                             'post_id' => $this->post->id
                         ]);

        $response->assertStatus(403);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function regular_user_cannot_update_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->actingAs($this->user)
                         ->put("/admin/comments/{$comment->id}", [
                             'author' => 'Updated Author',
                             'content' => 'Updated content',
                             'post_id' => $this->post->id
                         ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function regular_user_cannot_delete_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->actingAs($this->user)
                         ->delete("/admin/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function guest_cannot_create_comment()
    {
        $response = $this->post('/admin/comments', [
            'author' => 'Test Author',
            'content' => 'Test content',
            'post_id' => $this->post->id
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function guest_cannot_update_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->put("/admin/comments/{$comment->id}", [
            'author' => 'Updated Author',
            'content' => 'Updated content',
            'post_id' => $this->post->id
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function guest_cannot_delete_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->delete("/admin/comments/{$comment->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function admin_can_handle_special_characters_in_comment()
    {
        $commentData = [
            'author' => 'Author with Special Chars: àáâãäåæçèéêë & symbols 🚀',
            'content' => 'Content with émojis 🎉 and symbols ©®™ and newlines\nSecond line',
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $commentData);

        $response->assertRedirect('/admin/comments')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', $commentData);
    }

    #[Test]
    public function comment_update_preserves_timestamps()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);
        $originalCreatedAt = $comment->created_at;

        // Ждем немного для изменения updated_at
        sleep(1);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/comments/{$comment->id}", [
                             'author' => 'Updated Author',
                             'content' => 'Updated content',
                             'post_id' => $this->post->id
                         ]);

        $response->assertRedirect('/admin/comments');

        $comment->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $comment->created_at->timestamp);
        $this->assertNotEquals($originalCreatedAt->timestamp, $comment->updated_at->timestamp);
    }

    #[Test]
    public function index_handles_empty_comments_list()
    {
        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200)
                ->assertViewIs('admin.comments.index')
                ->assertViewHas('comments');

        $comments = $response->viewData('comments');
        $this->assertCount(0, $comments);
    }

    #[Test]
    public function posts_are_ordered_by_title_in_forms()
    {
        $postZ = Post::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Zebra Post'
        ]);
        
        $postA = Post::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Alpha Post'
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/comments/create');

        $response->assertStatus(200);
        
        $posts = $response->viewData('posts');
        $postTitles = $posts->pluck('title')->toArray();
        
        $this->assertEquals('Alpha Post', $postTitles[0]);
        $this->assertContains('Zebra Post', $postTitles);
    }
}