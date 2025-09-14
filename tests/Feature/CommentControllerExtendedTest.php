<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommentControllerExtendedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_view_comments_index()
    {
        Comment::factory()->count(5)->create();

        $response = $this->get('/comments');

        $response->assertStatus(200);
        $response->assertViewIs('comments.index');
    }

    #[Test]
    public function comments_index_shows_empty_state_when_no_comments()
    {
        $response = $this->get('/comments');

        $response->assertStatus(200);
        // Проверяем, что таблица пустая (нет строк в tbody)
        $response->assertSee('<tbody>', false); // false означает, что не нужно экранировать HTML
        $response->assertSee('</tbody>', false);
    }

    #[Test]
    public function comments_index_pagination_works()
    {
        // Создаем посты и комментарии без конфликтов
        $posts = Post::factory()->count(5)->create();
        Comment::factory()->count(25)->create();

        $response = $this->get('/comments');

        $response->assertStatus(200);
        $response->assertViewIs('comments.index');
    }

    #[Test]
    public function comments_are_ordered_by_latest()
    {
        $firstComment = Comment::factory()->create(['created_at' => now()->subDay()]);
        $secondComment = Comment::factory()->create(['created_at' => now()]);

        $response = $this->get('/comments');

        $response->assertStatus(200);
        $comments = $response->viewData('comments');
        $this->assertEquals($secondComment->id, $comments->first()->id);
    }

    #[Test]
    public function can_view_create_comment_form()
    {
        $response = $this->get('/comments/create');

        $response->assertStatus(200);
        $response->assertViewIs('comments.create');
    }

    #[Test]
    public function create_form_shows_posts_in_alphabetical_order()
    {
        // Создаем категорию для постов
        $category = Category::factory()->create();
        
        // Создаем посты с конкретными названиями
        $post1 = Post::factory()->create(['title' => 'Zebra Post', 'category_id' => $category->id]);
        $post2 = Post::factory()->create(['title' => 'Alpha Post', 'category_id' => $category->id]);
        $post3 = Post::factory()->create(['title' => 'Beta Post', 'category_id' => $category->id]);

        $response = $this->get('/comments/create');

        $response->assertStatus(200);
        $posts = $response->viewData('posts');

        $postTitles = $posts->pluck('title')->toArray();
        $this->assertEquals(['Alpha Post', 'Beta Post', 'Zebra Post'], $postTitles);
    }

    // === Новые тесты для метода show ===

    #[Test]
    public function can_view_single_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->get("/comments/{$comment->id}");

        $response->assertStatus(200);
        $response->assertViewIs('comments.show');
        $response->assertViewHas('comment');
        
        // Проверяем, что комментарий загружен с связями
        $viewComment = $response->viewData('comment');
        $this->assertTrue($viewComment->relationLoaded('post'));
    }

    #[Test]
    public function comment_show_loads_post_relationship()
    {
        $comment = Comment::factory()->create();

        $response = $this->get("/comments/{$comment->id}");

        $response->assertStatus(200);
        $comment = $response->viewData('comment');
        $this->assertTrue($comment->relationLoaded('post'));
        $this->assertInstanceOf(Post::class, $comment->post);
    }

    #[Test]
    public function comment_show_displays_comment_data()
    {
        $comment = Comment::factory()->create([
            'author' => 'Test Author',
            'content' => 'Test comment content'
        ]);

        $response = $this->get("/comments/{$comment->id}");

        $response->assertStatus(200);
        $response->assertSee('Test Author');
        $response->assertSee('Test comment content');
    }

    #[Test]
    public function comment_show_displays_related_post_info()
    {
        $post = Post::factory()->create(['title' => 'Related Post Title']);
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->get("/comments/{$comment->id}");

        $response->assertStatus(200);
        $response->assertSee('Related Post Title');
    }

    #[Test]
    public function comment_show_returns_404_for_non_existent_comment()
    {
        $response = $this->get('/comments/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function can_create_comment()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => 'Test comment content',
            'post_id' => $post->id,
        ]);

        // Контроллер редиректит на страницу поста
        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'content' => 'Test comment content',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_validates_required_fields()
    {
        $response = $this->post('/comments', []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
    }

    #[Test]
    public function create_comment_validates_author_length()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => str_repeat('a', 256), // Слишком длинное имя
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);

        $response->assertSessionHasErrors(['author']);
    }

    #[Test]
    public function create_comment_validates_content_length()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => str_repeat('a', 1001), // Слишком длинный контент (максимум 1000)
            'post_id' => $post->id,
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    #[Test]
    public function create_comment_validates_post_exists()
    {
        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => 'Test content',
            'post_id' => 999, // Несуществующий пост
        ]);

        $response->assertSessionHasErrors(['post_id']);
    }

    #[Test]
    public function comments_index_loads_post_relationships()
    {
        $comment = Comment::factory()->create();

        $response = $this->get('/comments');

        $response->assertStatus(200);
        $comments = $response->viewData('comments');
        $this->assertTrue($comments->first()->relationLoaded('post'));
    }

    #[Test]
    public function can_create_comment_with_unicode_content()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Тестовый Автор',
            'content' => 'Это тестовый комментарий с кириллицей',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Тестовый Автор',
            'content' => 'Это тестовый комментарий с кириллицей',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function can_create_comment_with_html_content()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => '<p>Test <strong>HTML</strong> content</p>',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'content' => '<p>Test <strong>HTML</strong> content</p>',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function comment_author_can_be_very_short()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'A',
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'A',
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function comment_content_can_be_very_short()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => 'Hi',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'content' => 'Hi',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function comment_content_can_be_maximum_length()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => str_repeat('a', 1000), // Максимальная длина (1000 символов)
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'content' => str_repeat('a', 1000),
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_handles_special_characters_in_author()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test-Author_123',
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test-Author_123',
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_handles_multiline_content()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => "Line 1\nLine 2\nLine 3",
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'content' => "Line 1\nLine 2\nLine 3",
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_handles_quotes_in_content()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Test Author',
            'content' => 'Test "quoted" content',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'content' => 'Test "quoted" content',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_handles_empty_strings_after_trimming()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => '   ',
            'content' => '   ',
            'post_id' => $post->id,
        ]);

        $response->assertSessionHasErrors(['author', 'content']);
    }

    #[Test]
    public function create_comment_handles_very_long_author_name()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => str_repeat('a', 255), // Максимальная длина имени
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => str_repeat('a', 255),
            'content' => 'Test content',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_handles_cyrillic_author()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'Иван Петров',
            'content' => 'Тестовый комментарий',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'Иван Петров',
            'content' => 'Тестовый комментарий',
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function create_comment_handles_mixed_languages()
    {
        $post = Post::factory()->create();

        $response = $this->post('/comments', [
            'author' => 'John Иванов',
            'content' => 'Hello Привет 你好',
            'post_id' => $post->id,
        ]);

        $response->assertRedirect("/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'author' => 'John Иванов',
            'content' => 'Hello Привет 你好',
            'post_id' => $post->id,
        ]);
    }

    // Тесты для методов, требующих аутентификации
    #[Test]
    public function guest_cannot_view_edit_comment_form()
    {
        $comment = Comment::factory()->create();

        $response = $this->get("/comments/{$comment->id}/edit");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function authenticated_user_can_view_edit_comment_form()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->get("/comments/{$comment->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('comments.edit');
    }

    #[Test]
    public function edit_form_shows_posts_in_alphabetical_order()
    {
        $user = User::factory()->create();
        
        // Создаем категорию для постов
        $category = Category::factory()->create();
        
        // Создаем посты с конкретными названиями
        $post1 = Post::factory()->create(['title' => 'Zebra Post', 'category_id' => $category->id]);
        $post2 = Post::factory()->create(['title' => 'Alpha Post', 'category_id' => $category->id]);
        $post3 = Post::factory()->create(['title' => 'Beta Post', 'category_id' => $category->id]);
        
        // Создаем комментарий после создания постов
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->get("/comments/{$comment->id}/edit");

        $response->assertStatus(200);
        $posts = $response->viewData('posts');

        $postTitles = $posts->pluck('title')->toArray();
        $this->assertEquals(['Alpha Post', 'Beta Post', 'Zebra Post'], $postTitles);
    }

    #[Test]
    public function guest_cannot_update_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->put("/comments/{$comment->id}", [
            'author' => 'Updated Author',
            'content' => 'Updated content',
            'post_id' => $comment->post_id,
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function authenticated_user_can_update_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->put("/comments/{$comment->id}", [
                'author' => 'Updated Author',
                'content' => 'Updated content',
                'post_id' => $comment->post_id,
            ]);

        // Контроллер редиректит на страницу поста
        $response->assertRedirect("/posts/{$comment->post_id}");
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'author' => 'Updated Author',
            'content' => 'Updated content',
        ]);
    }

    #[Test]
    public function update_comment_validates_required_fields()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->put("/comments/{$comment->id}", []);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
    }

    #[Test]
    public function update_comment_validates_unique_author()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->put("/comments/{$comment->id}", [
                'author' => str_repeat('a', 256), // Слишком длинное имя
                'content' => 'Updated content',
                'post_id' => $comment->post_id,
            ]);

        $response->assertSessionHasErrors(['author']);
    }

    #[Test]
    public function guest_cannot_delete_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->delete("/comments/{$comment->id}");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function authenticated_user_can_delete_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/comments/{$comment->id}");

        // Контроллер редиректит на страницу поста
        $response->assertRedirect("/posts/{$comment->post_id}");
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function delete_comment_returns_404_for_non_existent_comment()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete('/comments/999');

        $response->assertStatus(404);
    }
}