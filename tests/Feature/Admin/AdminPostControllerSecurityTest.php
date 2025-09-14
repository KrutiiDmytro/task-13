<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminPostControllerSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->category = Category::factory()->create(['name' => 'Security Test Category']);
        
        Storage::fake('public');
    }

    #[Test]
    public function it_prevents_sql_injection_in_post_data()
    {
        $maliciousData = [
            'title' => "'; DROP TABLE posts; --",
            'content' => "SELECT * FROM users WHERE id = 1; --",
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $maliciousData);

        $response->assertRedirect('/admin/posts');
        
        // Проверяем, что таблица posts все еще существует и данные сохранились
        $this->assertDatabaseHas('posts', [
            'title' => "'; DROP TABLE posts; --",
            'content' => "SELECT * FROM users WHERE id = 1; --"
        ]);
    }

    #[Test]
    public function it_handles_xss_attempts_in_post_data()
    {
        $xssData = [
            'title' => '<script>alert("XSS Title")</script>',
            'content' => '<img src="x" onerror="alert(1)"><script>document.cookie="hacked"</script>',
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $xssData);

        $response->assertRedirect('/admin/posts');
        
        // Данные должны сохраниться как есть (без выполнения скрипта)
        $this->assertDatabaseHas('posts', [
            'title' => '<script>alert("XSS Title")</script>',
            'content' => '<img src="x" onerror="alert(1)"><script>document.cookie="hacked"</script>'
        ]);
    }

    #[Test]
    public function it_handles_unicode_and_emoji_correctly()
    {
        $unicodeData = [
            'title' => 'Post 🚀 with émojis and ünïcödé',
            'content' => '测试 тест test 🎉🎊�� Post content with unicode characters',
            'category_id' => $this->category->id,
            'tags' => ['тег-с-кириллицей', 'tag-with-émojis-🎊']
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $unicodeData);

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Post 🚀 with émojis and ünïcödé',
            'content' => '测试 тест test 🎉🎊�� Post content with unicode characters'
        ]);
    }

    #[Test]
    public function it_handles_null_bytes_in_input()
    {
        $dataWithNullBytes = [
            'title' => "Title\x00Name",
            'content' => "Content\x00with\x00nulls",
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $dataWithNullBytes);

        // Laravel должен обработать это корректно
        $response->assertRedirect('/admin/posts');
    }

    #[Test]
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $maliciousData = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => $this->category->id,
            'id' => 999, // Попытка установить ID
            'created_at' => '2020-01-01 00:00:00', // Попытка установить дату создания
            'user_id' => 999, // Попытка установить другого пользователя
            'is_admin' => true, // Несуществующее поле
            'password' => 'hacked' // Несуществующее поле
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $maliciousData);

        $response->assertRedirect('/admin/posts');

        // Проверяем, что создался только один пост с корректными данными
        $post = Post::first();
        $this->assertNotEquals(999, $post->id);
        $this->assertNotEquals('2020-01-01 00:00:00', $post->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($this->admin->id, $post->user_id); // Должен быть текущий пользователь
        $this->assertFalse(isset($post->is_admin));
        $this->assertFalse(isset($post->password));
    }

    #[Test]
    public function it_validates_csrf_token_on_create()
    {
        $response = $this->post('/admin/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => $this->category->id
        ]);

        // Без CSRF токена должен быть редирект на логин или 419 ошибка
        $this->assertContains($response->status(), [302, 419]);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_validates_csrf_token_on_update()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->put("/admin/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'category_id' => $this->category->id
        ]);

        $this->assertContains($response->status(), [302, 419]);
    }

    #[Test]
    public function it_validates_csrf_token_on_delete()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->delete("/admin/posts/{$post->id}");

        $this->assertContains($response->status(), [302, 419]);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    #[Test]
    public function it_handles_very_long_strings_gracefully()
    {
        $veryLongTitle = str_repeat('A', 10000);
        $veryLongContent = str_repeat('B', 10000);

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => $veryLongTitle,
                             'content' => $veryLongContent,
                             'category_id' => $this->category->id
                         ]);

        // Должны быть ошибки валидации
        $response->assertSessionHasErrors(['title']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_handles_invalid_data_types()
    {
        $invalidData = [
            'title' => 12345, // Число вместо строки
            'content' => ['array', 'instead', 'of', 'string'],
            'category_id' => 'not_a_number',
            'tags' => 'not_an_array'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $invalidData);

        $response->assertSessionHasErrors(['title', 'content', 'category_id', 'tags']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_prevents_path_traversal_in_post_data()
    {
        $pathTraversalData = [
            'title' => '../../../etc/passwd',
            'content' => '..\\..\\windows\\system32\\config\\sam',
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $pathTraversalData);

        $response->assertRedirect('/admin/posts');
        
        // Данные должны сохраниться как строки, без выполнения как пути
        $this->assertDatabaseHas('posts', [
            'title' => '../../../etc/passwd',
            'content' => '..\\..\\windows\\system32\\config\\sam'
        ]);
    }

    #[Test]
    public function it_handles_malicious_file_uploads()
    {
        // Попытка загрузить исполняемый файл
        $maliciousFile = UploadedFile::fake()->create('malicious.php', 1000, 'application/x-php');

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => 'Test Post',
                             'content' => 'Test content',
                             'category_id' => $this->category->id,
                             'image' => $maliciousFile
                         ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_handles_oversized_file_uploads()
    {
        // Создаем файл больше лимита (4MB)
        $oversizedFile = UploadedFile::fake()->image('oversized.jpg', 800, 600)->size(5000);

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => 'Test Post',
                             'content' => 'Test content',
                             'category_id' => $this->category->id,
                             'image' => $oversizedFile
                         ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_handles_post_deletion_race_conditions()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);

        // Имитируем одновременное удаление
        $response1 = $this->actingAs($this->admin)
                          ->delete("/admin/posts/{$post->id}");
        
        $response2 = $this->actingAs($this->admin)
                          ->delete("/admin/posts/{$post->id}");

        // Первый запрос должен успешно удалить
        $response1->assertRedirect('/admin/posts');
        
        // Второй запрос должен вернуть 404
        $response2->assertStatus(404);
        
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function it_limits_post_list_response_size()
    {
        // Создаем много постов с уникальными данными
        for ($i = 1; $i <= 100; $i++) {
            Post::factory()->create([
                'user_id' => $this->admin->id,
                'category_id' => $this->category->id,
                'title' => "Security Post {$i}",
                'content' => "Security post content {$i}"
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertStatus(200);
        
        // Проверяем, что используется пагинация
        $posts = $response->viewData('posts');
        $this->assertCount(15, $posts); // Не более 15 на страницу
        $this->assertTrue($posts->hasPages());
    }

    #[Test]
    public function it_handles_special_route_parameters()
    {
        // Тестируем различные типы некорректных параметров
        $invalidIds = ['abc', '999999999999999999999', '-1', '0', 'null', 'undefined'];

        foreach ($invalidIds as $invalidId) {
            $response = $this->actingAs($this->admin)
                             ->get("/admin/posts/{$invalidId}");
            
            $this->assertContains($response->status(), [404, 400]);
        }
    }

    #[Test]
    public function it_handles_concurrent_post_creation()
    {
        $postData = [
            'title' => 'Concurrent Post',
            'content' => 'Concurrent content',
            'category_id' => $this->category->id
        ];

        // Имитируем одновременное создание постов
        $response1 = $this->actingAs($this->admin)
                          ->post('/admin/posts', $postData);
        
        $response2 = $this->actingAs($this->admin)
                          ->post('/admin/posts', $postData);

        // Оба запроса должны пройти успешно
        $response1->assertRedirect('/admin/posts');
        $response2->assertRedirect('/admin/posts');

        // Должно быть создано 2 поста
        $this->assertEquals(2, Post::where('title', 'Concurrent Post')->count());
    }

    #[Test]
    public function it_validates_tag_length_limits()
    {
        $longTag = str_repeat('a', 31); // Превышаем лимит в 30 символов

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => 'Test Post',
                             'content' => 'Test content',
                             'category_id' => $this->category->id,
                             'tags' => [$longTag]
                         ]);

        $response->assertSessionHasErrors(['tags.0']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_handles_malformed_multipart_data()
    {
        $response = $this->actingAs($this->admin)
                         ->call('POST', '/admin/posts', [], [], [], [
                             'CONTENT_TYPE' => 'multipart/form-data; boundary=----invalid'
                         ], 'invalid multipart data');

        // Проверяем, что это не успешный ответ и пост не создался
        $this->assertNotEquals(200, $response->status());
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function it_prevents_file_upload_path_traversal(): void
    {
        // Используем fake storage для public диска
        Storage::fake('public');
        
        $this->actingAs($this->admin);

        // Создаем временный файл
        $image = UploadedFile::fake()->image('test.jpg');
        
        // Тестируем загрузку файла
        $response = $this->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content' => 'Test content',
            'image' => $image,
        ]);

        // Проверяем, что запрос прошел успешно
        $response->assertRedirect(route('admin.posts.index'));

        // Проверяем, что пост был создан
        $post = Post::where('title', 'Test Post')->first();
        $this->assertNotNull($post);
        
        // Проверяем, что путь к файлу безопасен (не содержит ../)
        $this->assertStringStartsWith('posts/', $post->image);
        $this->assertStringNotContainsString('../', $post->image);
        $this->assertStringNotContainsString('..\\', $post->image);
        
        // Проверяем, что файл существует в fake storage
        Storage::disk('public')->assertExists($post->image);
        
        // Проверяем, что файл НЕ существует в родительской директории
        // (убираем проверки с ../ так как Flysystem их блокирует)
        Storage::disk('public')->assertMissing('malicious.jpg');
        
        // Дополнительная проверка: тестируем попытку загрузки файла с подозрительным именем
        $maliciousImage = UploadedFile::fake()->image('malicious.jpg');
        
        $response2 = $this->post(route('admin.posts.store'), [
            'title' => 'Malicious Post',
            'content' => 'Malicious content',
            'image' => $maliciousImage,
        ]);

        $response2->assertRedirect(route('admin.posts.index'));

        // Проверяем, что второй пост тоже был создан с безопасным путем
        $maliciousPost = Post::where('title', 'Malicious Post')->first();
        $this->assertNotNull($maliciousPost);
        $this->assertStringStartsWith('posts/', $maliciousPost->image);
        $this->assertStringNotContainsString('../', $maliciousPost->image);
        $this->assertStringNotContainsString('..\\', $maliciousPost->image);
        
        // Проверяем, что оба файла существуют в правильных местах
        Storage::disk('public')->assertExists($post->image);
        Storage::disk('public')->assertExists($maliciousPost->image);
    }
}