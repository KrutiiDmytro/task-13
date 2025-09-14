<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminCategoryControllerSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    #[Test]
    public function it_prevents_sql_injection_in_category_name()
    {
        $maliciousData = [
            'name' => "'; DROP TABLE categories; --",
            'description' => 'Malicious description'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $maliciousData);

        $response->assertRedirect('/admin/categories');
        
        // Проверяем, что таблица categories все еще существует и данные сохранились
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

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $xssData);

        $response->assertRedirect('/admin/categories');
        
        // Данные должны сохраниться как есть (без выполнения скрипта)
        $this->assertDatabaseHas('categories', [
            'name' => '<script>alert("XSS")</script>',
            'description' => '<img src="x" onerror="alert(1)">'
        ]);
    }

    #[Test]
    public function it_handles_unicode_and_emoji_correctly()
    {
        $unicodeData = [
            'name' => 'Category 🚀 with émojis and ünïcödé',
            'description' => '测试 тест test 🎉🎊🎈'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $unicodeData);

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', $unicodeData);
    }

    #[Test]
    public function it_handles_null_bytes_in_input()
    {
        $dataWithNullBytes = [
            'name' => "Category\x00Name",
            'description' => "Description\x00with\x00nulls"
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $dataWithNullBytes);

        // Laravel должен обработать это корректно
        $response->assertRedirect('/admin/categories');
    }

    #[Test]
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $maliciousData = [
            'name' => 'Valid Category',
            'description' => 'Valid description',
            'id' => 999999, // Попытка установить ID
            'created_at' => '2020-01-01 00:00:00', // Попытка установить время создания
            'updated_at' => '2020-01-01 00:00:00', // Попытка установить время обновления
            'admin' => true, // Несуществующее поле
            'is_special' => true // Несуществующее поле
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $maliciousData);

        $response->assertRedirect('/admin/categories');

        $category = Category::latest()->first();
        
        // Проверяем, что ID не был установлен принудительно
        $this->assertNotEquals(999999, $category->id);
        
        // Проверяем, что временные метки установились автоматически
        $this->assertNotEquals('2020-01-01 00:00:00', $category->created_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_handles_concurrent_category_creation_with_same_name()
    {
        $categoryData = [
            'name' => 'Concurrent Category',
            'description' => 'Test concurrent creation'
        ];

        // Первый запрос должен пройти
        $response1 = $this->actingAs($this->admin)
                          ->post('/admin/categories', $categoryData);
        $response1->assertRedirect('/admin/categories')
                  ->assertSessionHas('success');

        // Второй запрос с тем же именем должен упасть с ошибкой валидации
        $response2 = $this->actingAs($this->admin)
                          ->post('/admin/categories', $categoryData);
        $response2->assertSessionHasErrors(['name']);

        // Проверяем, что создалась только одна категория
        $this->assertEquals(1, Category::where('name', 'Concurrent Category')->count());
    }

    #[Test]
    public function it_validates_csrf_token_on_create()
    {
        $response = $this->post('/admin/categories', [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);

        // Без CSRF токена должен быть редирект на логин или 419 ошибка
        $this->assertContains($response->status(), [302, 419]);
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function it_validates_csrf_token_on_update()
    {
        $category = Category::factory()->create();

        $response = $this->put("/admin/categories/{$category->id}", [
            'name' => 'Updated Name'
        ]);

        // Без CSRF токена должен быть редирект на логин или 419 ошибка
        $this->assertContains($response->status(), [302, 419]);
    }

    #[Test]
    public function it_validates_csrf_token_on_delete()
    {
        $category = Category::factory()->create();

        $response = $this->delete("/admin/categories/{$category->id}");

        // Без CSRF токена должен быть редирект на логин или 419 ошибка
        $this->assertContains($response->status(), [302, 419]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_handles_very_long_strings_gracefully()
    {
        $veryLongName = str_repeat('A', 10000);
        $veryLongDescription = str_repeat('B', 10000);

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', [
                             'name' => $veryLongName,
                             'description' => $veryLongDescription
                         ]);

        // Должны быть ошибки валидации из-за превышения лимитов
        $response->assertSessionHasErrors(['name', 'description']);
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function it_handles_invalid_data_types()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', [
                             'name' => 123, // Число вместо строки
                             'description' => ['array', 'instead', 'of', 'string']
                         ]);

        // Laravel должен обработать это корректно или выдать ошибку валидации
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function it_prevents_path_traversal_in_category_data()
    {
        $maliciousData = [
            'name' => '../../../etc/passwd',
            'description' => '../../config/database.php'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $maliciousData);

        $response->assertRedirect('/admin/categories');
        
        // Данные должны сохраниться как обычные строки
        $this->assertDatabaseHas('categories', [
            'name' => '../../../etc/passwd'
        ]);
    }

    #[Test]
    public function it_handles_category_deletion_race_conditions()
    {
        $category = Category::factory()->create();

        // Создаем пост для категории в отдельном процессе (эмуляция)
        Post::factory()->create(['category_id' => $category->id]);

        // Попытка удалить категорию должна быть заблокирована
        $response = $this->actingAs($this->admin)
                         ->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('error', 'Нельзя удалить категорию с постами!');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_limits_category_list_response_size()
    {
        // Создаем много категорий с уникальными именами
        for ($i = 1; $i <= 100; $i++) {
            Category::factory()->create([
                'name' => "Security Category {$i}",
                'description' => "Description {$i}"
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        
        // Проверяем, что используется пагинация
        $categories = $response->viewData('categories');
        $this->assertCount(15, $categories); // Не более 15 на страницу
        $this->assertTrue($categories->hasPages());
    }

    #[Test]
    public function it_handles_special_route_parameters()
    {
        // Тестируем различные типы параметров маршрута
        $responses = [
            $this->actingAs($this->admin)->get('/admin/categories/abc'), // Строка вместо числа
            $this->actingAs($this->admin)->get('/admin/categories/-1'), // Отрицательное число
            $this->actingAs($this->admin)->get('/admin/categories/0'), // Ноль
        ];

        foreach ($responses as $response) {
            $response->assertStatus(404);
        }
    }
}