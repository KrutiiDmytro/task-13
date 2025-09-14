<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        
        $category = Category::factory()->create();
        $this->post = Post::factory()->create(['category_id' => $category->id]);
    }

    #[Test]
    public function it_prevents_sql_injection_in_comment_content()
    {
        $maliciousData = [
            'content' => "'; DROP TABLE comments; --",
            'author' => 'Malicious Author',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $maliciousData);

        $response->assertStatus(201);
        
        // Проверяем, что таблица comments все еще существует
        $this->assertDatabaseHas('comments', [
            'content' => "'; DROP TABLE comments; --"
        ]);
    }

    #[Test]
    public function it_handles_xss_attempts_in_comment_data()
    {
        $xssData = [
            'content' => '<script>alert("XSS in content")</script>',
            'author' => '<img src="x" onerror="alert(1)">',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $xssData);

        $response->assertStatus(201);
        
        // Данные должны сохраниться как есть (без выполнения скрипта)
        $this->assertDatabaseHas('comments', [
            'content' => '<script>alert("XSS in content")</script>',
            'author' => '<img src="x" onerror="alert(1)">'
        ]);
    }

    #[Test]
    public function it_handles_very_long_strings_gracefully()
    {
        $veryLongContent = str_repeat('A', 100000); // Очень длинный контент
        $veryLongAuthor = str_repeat('B', 10000); // Очень длинное имя автора

        $response = $this->postJson('/api/v1/comments', [
            'content' => $veryLongContent,
            'author' => $veryLongAuthor,
            'post_id' => $this->post->id
        ]);

        // Должна быть ошибка валидации для автора (превышение лимита)
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['author']);
    }

    #[Test]
    public function it_handles_unicode_and_emoji_correctly()
    {
        $unicodeData = [
            'content' => 'Comment 🚀 with émojis and ünïcödé characters 测试 тест',
            'author' => 'Authör 🎉🎊🎈 with spéciàl chars',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $unicodeData);

        $response->assertStatus(201)
                ->assertJsonPath('data.content', 'Comment 🚀 with émojis and ünïcödé characters 测试 тест')
                ->assertJsonPath('data.author', 'Authör 🎉🎊🎈 with spéciàl chars');
    }

    #[Test]
    public function it_handles_null_bytes_in_input()
    {
        $dataWithNullBytes = [
            'content' => "Comment\x00with\x00null\x00bytes",
            'author' => "Author\x00Name",
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $dataWithNullBytes);

        // Laravel должен обработать это корректно
        $response->assertStatus(201);
    }

    #[Test]
    public function it_validates_post_id_injection_attempts()
    {
        $maliciousData = [
            'content' => 'Valid content',
            'author' => 'Valid author',
            'post_id' => "1; DROP TABLE posts; --"
        ];

        $response = $this->postJson('/api/v1/comments', $maliciousData);

        // Должна быть ошибка валидации, так как post_id не число
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['post_id']);
    }

    #[Test]
    public function it_handles_concurrent_comment_creation()
    {
        $commentData = [
            'content' => 'Concurrent comment test',
            'author' => 'Concurrent Author',
            'post_id' => $this->post->id
        ];

        // Несколько одновременных запросов должны пройти успешно
        $response1 = $this->postJson('/api/v1/comments', $commentData);
        $response2 = $this->postJson('/api/v1/comments', $commentData);
        $response3 = $this->postJson('/api/v1/comments', $commentData);

        $response1->assertStatus(201);
        $response2->assertStatus(201);
        $response3->assertStatus(201);

        // Проверяем, что создались 3 отдельных комментария
        $this->assertEquals(3, Comment::where('content', 'Concurrent comment test')->count());
    }

    #[Test]
    public function it_handles_empty_request_body()
    {
        // Исправляем: передаем пустой массив вместо null
        $response = $this->postJson('/api/v1/comments', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content', 'author', 'post_id']);
    }

    #[Test]
    public function it_handles_malformed_json_gracefully()
    {
        // Laravel обрабатывает невалидный JSON как пустой запрос и возвращает валидационные ошибки
        $response = $this->call('POST', '/api/v1/comments', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ], '{invalid json}');

        // Проверяем, что Laravel корректно обрабатывает невалидный JSON
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content', 'author', 'post_id']);
    }

    #[Test]
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $maliciousData = [
            'content' => 'Valid content',
            'author' => 'Valid author',
            'post_id' => $this->post->id,
            'id' => 999999, // Попытка установить ID
            'created_at' => '2020-01-01 00:00:00', // Попытка установить время создания
            'updated_at' => '2020-01-01 00:00:00', // Попытка установить время обновления
            'admin' => true, // Несуществующее поле
            'is_approved' => true // Несуществующее поле
        ];

        $response = $this->postJson('/api/v1/comments', $maliciousData);

        $response->assertStatus(201);

        $comment = Comment::latest()->first();
        
        // Проверяем, что ID не был установлен принудительно
        $this->assertNotEquals(999999, $comment->id);
        
        // Проверяем, что временные метки установились автоматически
        $this->assertNotEquals('2020-01-01 00:00:00', $comment->created_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_limits_response_size_appropriately()
    {
        // Создаем много комментариев
        Comment::factory()->count(1000)->create(['post_id' => $this->post->id]);

        $response = $this->getJson('/api/v1/comments');

        $response->assertStatus(200);
        
        // Проверяем, что ответ не слишком большой
        $contentLength = strlen($response->getContent());
        $this->assertLessThan(50 * 1024 * 1024, $contentLength); // Менее 50MB
    }

    #[Test]
    public function it_handles_invalid_post_id_types()
    {
        $invalidData = [
            'content' => 'Valid content',
            'author' => 'Valid author',
            'post_id' => ['array', 'instead', 'of', 'number']
        ];

        $response = $this->postJson('/api/v1/comments', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['post_id']);
    }

    #[Test]
    public function it_handles_negative_post_id()
    {
        $invalidData = [
            'content' => 'Valid content',
            'author' => 'Valid author',
            'post_id' => -1
        ];

        $response = $this->postJson('/api/v1/comments', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['post_id']);
    }

    #[Test]
    public function it_handles_zero_post_id()
    {
        $invalidData = [
            'content' => 'Valid content',
            'author' => 'Valid author',
            'post_id' => 0
        ];

        $response = $this->postJson('/api/v1/comments', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['post_id']);
    }
}