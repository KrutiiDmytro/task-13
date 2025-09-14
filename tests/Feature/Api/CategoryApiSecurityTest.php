<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_prevents_sql_injection_in_category_name()
    {
        $maliciousData = [
            'name' => "'; DROP TABLE categories; --",
            'description' => 'Malicious description'
        ];

        $response = $this->postJson('/api/v1/categories', $maliciousData);

        $response->assertStatus(201);
        
        // Проверяем, что таблица categories все еще существует
        $this->assertDatabaseHas('categories', [
            'name' => "'; DROP TABLE categories; --"
        ]);
    }

    #[Test]
    public function it_handles_xss_attempts_in_category_data()
    {
        $xssData = [
            'name' => '<script>alert("XSS")</script>',
            'description' => '<img src="x" onerror="alert(1)">'
        ];

        $response = $this->postJson('/api/v1/categories', $xssData);

        $response->assertStatus(201);
        
        // Данные должны сохраниться как есть (без выполнения скрипта)
        $this->assertDatabaseHas('categories', [
            'name' => '<script>alert("XSS")</script>'
        ]);
    }

    #[Test]
    public function it_handles_very_long_strings_gracefully()
    {
        $longString = str_repeat('A', 10000); // Очень длинная строка

        $response = $this->postJson('/api/v1/categories', [
            'name' => $longString,
            'description' => $longString
        ]);

        // Должна быть ошибка валидации из-за превышения лимита
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_handles_unicode_and_emoji_correctly()
    {
        $unicodeData = [
            'name' => 'Category 🚀 with émojis and ünïcödé',
            'description' => '测试 тест test 🎉🎊🎈'
        ];

        $response = $this->postJson('/api/v1/categories', $unicodeData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Category 🚀 with émojis and ünïcödé');
    }

    #[Test]
    public function it_handles_null_bytes_in_input()
    {
        $dataWithNullBytes = [
            'name' => "Category\x00Name",
            'description' => "Description\x00with\x00nulls"
        ];

        $response = $this->postJson('/api/v1/categories', $dataWithNullBytes);

        // Laravel должен обработать это корректно
        $response->assertStatus(201);
    }

    #[Test]
    public function it_handles_concurrent_category_creation_with_same_name()
    {
        $categoryData = [
            'name' => 'Concurrent Category',
            'description' => 'Test concurrent creation'
        ];

        // Первый запрос должен пройти
        $response1 = $this->postJson('/api/v1/categories', $categoryData);
        $response1->assertStatus(201);

        // Второй запрос с тем же именем должен упасть
        $response2 = $this->postJson('/api/v1/categories', $categoryData);
        $response2->assertStatus(422)
                  ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_validates_input_types_strictly()
    {
        $invalidTypeData = [
            'name' => 123, // Число вместо строки
            'description' => ['array', 'instead', 'of', 'string']
        ];

        $response = $this->postJson('/api/v1/categories', $invalidTypeData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_handles_empty_request_body()
    {
        $response = $this->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_handles_malformed_json_gracefully()
    {
        $response = $this->call('POST', '/api/v1/categories', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ], '{invalid json}');

        $response->assertStatus(422) // Laravel обрабатывает как валидационную ошибку
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_limits_response_size_appropriately()
    {
        // Создаем много категорий с уникальными именами
        for ($i = 1; $i <= 1000; $i++) {
            Category::factory()->create([
                'name' => "API Security Category {$i}",
                'description' => "Long description {$i}"
            ]);
        }

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        
        // Проверяем, что ответ не слишком большой
        $contentLength = strlen($response->getContent());
        $this->assertLessThan(10 * 1024 * 1024, $contentLength); // Менее 10MB
    }
}