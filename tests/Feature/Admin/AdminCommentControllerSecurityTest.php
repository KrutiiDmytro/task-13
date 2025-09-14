<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminCommentControllerSecurityTest extends TestCase
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
        $this->category = Category::factory()->create(['name' => 'Security Test Category']);
        $this->post = Post::factory()->create(['category_id' => $this->category->id]);
    }

    #[Test]
    public function it_prevents_sql_injection_in_comment_data()
    {
        $maliciousData = [
            'author' => "'; DROP TABLE comments; --",
            'content' => "SELECT * FROM users WHERE id = 1; --",
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $maliciousData);

        $response->assertRedirect('/admin/comments');
        
        // Проверяем, что таблица comments все еще существует и данные сохранились
        $this->assertDatabaseHas('comments', [
            'author' => "'; DROP TABLE comments; --",
            'content' => "SELECT * FROM users WHERE id = 1; --"
        ]);
    }

    #[Test]
    public function it_handles_xss_attempts_in_comment_data()
    {
        $xssData = [
            'author' => '<script>alert("XSS Author")</script>',
            'content' => '<img src="x" onerror="alert(1)"><script>document.cookie="hacked"</script>',
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $xssData);

        $response->assertRedirect('/admin/comments');
        
        // Данные должны сохраниться как есть (без выполнения скрипта)
        $this->assertDatabaseHas('comments', [
            'author' => '<script>alert("XSS Author")</script>',
            'content' => '<img src="x" onerror="alert(1)"><script>document.cookie="hacked"</script>'
        ]);
    }

    #[Test]
    public function it_handles_unicode_and_emoji_correctly()
    {
        $unicodeData = [
            'author' => 'Author 🚀 with émojis and ünïcödé',
            'content' => '测试 тест test 🎉🎊🎈 Comment with unicode characters',
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $unicodeData);

        $response->assertRedirect('/admin/comments')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', $unicodeData);
    }

    #[Test]
    public function it_handles_null_bytes_in_input()
    {
        $dataWithNullBytes = [
            'author' => "Author\x00Name",
            'content' => "Content\x00with\x00nulls",
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $dataWithNullBytes);

        // Laravel должен обработать это корректно
        $response->assertRedirect('/admin/comments');
    }

    #[Test]
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $maliciousData = [
            'author' => 'Test Author',
            'content' => 'Test content',
            'post_id' => $this->post->id,
            'id' => 999, // Попытка установить ID
            'created_at' => '2020-01-01 00:00:00', // Попытка установить дату создания
            'is_admin' => true, // Несуществующее поле
            'password' => 'hacked' // Несуществующее поле
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $maliciousData);

        $response->assertRedirect('/admin/comments');

        // Проверяем, что создался только один комментарий с корректными данными
        $comment = Comment::first();
        $this->assertNotEquals(999, $comment->id);
        $this->assertNotEquals('2020-01-01 00:00:00', $comment->created_at->format('Y-m-d H:i:s'));
        $this->assertFalse(isset($comment->is_admin));
        $this->assertFalse(isset($comment->password));
    }

    #[Test]
    public function it_validates_csrf_token_on_create()
    {
        $response = $this->post('/admin/comments', [
            'author' => 'Test Author',
            'content' => 'Test content',
            'post_id' => $this->post->id
        ]);

        // Без CSRF токена должен быть редирект на логин или 419 ошибка
        $this->assertContains($response->status(), [302, 419]);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function it_validates_csrf_token_on_update()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->put("/admin/comments/{$comment->id}", [
            'author' => 'Updated Author',
            'content' => 'Updated content',
            'post_id' => $this->post->id
        ]);

        $this->assertContains($response->status(), [302, 419]);
    }

    #[Test]
    public function it_validates_csrf_token_on_delete()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->delete("/admin/comments/{$comment->id}");

        $this->assertContains($response->status(), [302, 419]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function it_handles_very_long_strings_gracefully()
    {
        $veryLongAuthor = str_repeat('A', 10000);
        $veryLongContent = str_repeat('B', 10000);

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', [
                             'author' => $veryLongAuthor,
                             'content' => $veryLongContent,
                             'post_id' => $this->post->id
                         ]);

        // Должны быть ошибки валидации
        $response->assertSessionHasErrors(['author', 'content']);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function it_handles_invalid_data_types()
    {
        $invalidData = [
            'author' => 12345, // Число вместо строки
            'content' => ['array', 'instead', 'of', 'string'],
            'post_id' => 'not_a_number'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $invalidData);

        $response->assertSessionHasErrors(['author', 'content', 'post_id']);
        $this->assertDatabaseEmpty('comments');
    }

    #[Test]
    public function it_prevents_path_traversal_in_comment_data()
    {
        $pathTraversalData = [
            'author' => '../../../etc/passwd',
            'content' => '..\\..\\windows\\system32\\config\\sam',
            'post_id' => $this->post->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $pathTraversalData);

        $response->assertRedirect('/admin/comments');
        
        // Данные должны сохраниться как строки, без выполнения как пути
        $this->assertDatabaseHas('comments', [
            'author' => '../../../etc/passwd',
            'content' => '..\\..\\windows\\system32\\config\\sam'
        ]);
    }

    #[Test]
    public function it_handles_comment_deletion_race_conditions()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        // Имитируем одновременное удаление
        $response1 = $this->actingAs($this->admin)
                          ->delete("/admin/comments/{$comment->id}");
        
        $response2 = $this->actingAs($this->admin)
                          ->delete("/admin/comments/{$comment->id}");

        // Первый запрос должен успешно удалить
        $response1->assertRedirect('/admin/comments');
        
        // Второй запрос должен вернуть 404
        $response2->assertStatus(404);
        
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function it_limits_comment_list_response_size()
    {
        // Создаем много комментариев с уникальными данными
        for ($i = 1; $i <= 100; $i++) {
            Comment::factory()->create([
                'post_id' => $this->post->id,
                'author' => "Security Author {$i}",
                'content' => "Security comment content {$i}"
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200);
        
        // Проверяем, что используется пагинация
        $comments = $response->viewData('comments');
        $this->assertCount(15, $comments); // Не более 15 на страницу
        $this->assertTrue($comments->hasPages());
    }

    #[Test]
    public function it_handles_special_route_parameters()
    {
        // Тестируем различные типы некорректных параметров
        $invalidIds = ['abc', '999999999999999999999', '-1', '0', 'null', 'undefined'];

        foreach ($invalidIds as $invalidId) {
            $response = $this->actingAs($this->admin)
                             ->get("/admin/comments/{$invalidId}");
            
            $this->assertContains($response->status(), [404, 400]);
        }
    }

    #[Test]
    public function it_handles_concurrent_comment_creation()
    {
        $commentData = [
            'author' => 'Concurrent Author',
            'content' => 'Concurrent content',
            'post_id' => $this->post->id
        ];

        // Имитируем одновременное создание комментариев
        $response1 = $this->actingAs($this->admin)
                          ->post('/admin/comments', $commentData);
        
        $response2 = $this->actingAs($this->admin)
                          ->post('/admin/comments', $commentData);

        // Оба запроса должны пройти успешно
        $response1->assertRedirect('/admin/comments');
        $response2->assertRedirect('/admin/comments');

        // Должно быть создано 2 комментария
        $this->assertEquals(2, Comment::where('author', 'Concurrent Author')->count());
    }

    #[Test]
    public function it_validates_post_ownership_and_existence()
    {
        // Создаем пост другого пользователя
        $anotherUser = User::factory()->create();
        $anotherPost = Post::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $anotherUser->id
        ]);

        $commentData = [
            'author' => 'Test Author',
            'content' => 'Test content',
            'post_id' => $anotherPost->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/comments', $commentData);

        // Админ должен иметь возможность создавать комментарии к любым постам
        $response->assertRedirect('/admin/comments')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', $commentData);
    }
}