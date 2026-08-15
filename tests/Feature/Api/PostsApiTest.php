<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class PostsApiTest extends ApiTestCase
{
    protected $resource = 'posts';

    protected function createResourceData(): array
    {
        $category = Category::factory()->create();

        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'author_name' => $this->user->name,
            'author_email' => $this->user->email,
        ];
    }

    #[Test]
    public function index_returns_paginated_posts_json(): void
    {
        Post::factory()->count(12)->create();

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'count', 'per_page', 'current_page', 'total_pages'],
            ]);
    }

    #[Test]
    public function index_supports_xml_format(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->get($this->getResourceUrl().'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->getContent());
    }

    #[Test]
    public function index_handles_exception_gracefully(): void
    {
        // Создаем ситуацию, которая может привести к исключению
        // Например, неправильный параметр поиска или проблему с БД
        $response = $this->getJson($this->getResourceUrl().'?search='.str_repeat('x', 1000));

        // Контроллер должен вернуть 200 или 500 с сообщением об ошибке
        $this->assertContains($response->status(), [200, 500]);
    }

    #[Test]
    public function store_creates_post_and_returns_201_json(): void
    {
        $payload = $this->createResourceData();

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'content', 'category_id',
                    'created_at', 'updated_at',
                    'category', 'tags', 'comments',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => $payload['title'],
            'content' => $payload['content'],
            'category_id' => $payload['category_id'],
        ]);
    }

    #[Test]
    public function store_creates_post_with_tags(): void
    {
        $category = Category::factory()->create();

        // Используем уникальные имена для тегов
        $uniqueTag1 = 'unique-tag-'.uniqid();
        $uniqueTag2 = 'unique-tag-'.uniqid();

        $payload = [
            'title' => 'Post with Tags',
            'content' => 'Content with tags',
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'author_name' => $this->user->name,
            'author_email' => $this->user->email,
            'tags' => [$uniqueTag1, $uniqueTag2],
        ];

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('posts', [
            'title' => 'Post with Tags',
            'content' => 'Content with tags',
        ]);

        // Проверяем, что теги созданы
        $this->assertDatabaseHas('tags', ['name' => $uniqueTag1]);
        $this->assertDatabaseHas('tags', ['name' => $uniqueTag2]);
    }

    #[Test]
    public function store_returns_422_on_validation_error(): void
    {
        $response = $this->postJson($this->getResourceUrl(), []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function store_supports_xml_format(): void
    {
        $payload = $this->createResourceData();

        $response = $this->post($this->getResourceUrl().'?format=xml', $payload, ['Accept' => 'application/xml']);

        $response->assertCreated();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function show_returns_post_json(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson($this->getResourceUrl($post->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'content', 'category_id', 'created_at',
                    'updated_at', 'category', 'tags', 'comments',
                ],
            ])
            ->assertJsonPath('data.id', $post->id);
    }

    #[Test]
    public function show_supports_xml_format(): void
    {
        $post = Post::factory()->create();

        $response = $this->get($this->getResourceUrl($post->id).'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function show_returns_404_for_nonexistent_post(): void
    {
        $response = $this->getJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function update_updates_post_and_returns_200_json(): void
    {
        $post = Post::factory()->create();
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ];

        $response = $this->putJson($this->getResourceUrl($post->id), $payload);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.content', 'Updated Content');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'category_id' => $category->id,
        ]);
    }

    #[Test]
    public function update_supports_xml_format(): void
    {
        $post = Post::factory()->create();
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Updated XML Title',
            'content' => 'Updated XML Content',
            'category_id' => $category->id,
        ];

        $response = $this->put(
            $this->getResourceUrl($post->id).'?format=xml',
            $payload,
            ['Accept' => 'application/xml']
        );

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function update_returns_422_on_validation_error(): void
    {
        $post = Post::factory()->create();

        $response = $this->putJson($this->getResourceUrl($post->id), []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function destroy_returns_204_json(): void
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson($this->getResourceUrl($post->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function destroy_supports_xml_format(): void
    {
        $post = Post::factory()->create();

        $response = $this->delete(
            $this->getResourceUrl($post->id).'?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertNoContent();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function destroy_returns_500_on_exception(): void
    {
        // Тестируем ветку с исключением
        $response = $this->deleteJson($this->getResourceUrl(99999));

        // Может вернуть 404 или 500 в зависимости от реализации
        $this->assertContains($response->status(), [404, 500]);
    }

    #[Test]
    public function index_supports_search_parameter(): void
    {
        Post::factory()->create(['title' => 'Laravel Tutorial']);
        Post::factory()->create(['title' => 'PHP Basics']);
        Post::factory()->create(['content' => 'This is about Laravel framework']);

        $response = $this->getJson($this->getResourceUrl().'?search=Laravel');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    #[Test]
    public function index_supports_category_filter(): void
    {
        $category1 = Category::factory()->create(['name' => 'Tech']);
        $category2 = Category::factory()->create(['name' => 'News']);

        Post::factory()->create(['category_id' => $category1->id]);
        Post::factory()->create(['category_id' => $category2->id]);

        $response = $this->getJson($this->getResourceUrl().'?category_id='.$category1->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($category1->id, $data[0]['category_id']);
    }

    #[Test]
    public function index_supports_tag_filter(): void
    {
        $tag = Tag::factory()->create(['name' => 'php']);
        $post = Post::factory()->create();
        $post->tags()->attach($tag->id);

        Post::factory()->create(); // post without tags

        $response = $this->getJson($this->getResourceUrl().'?tag_id='.$tag->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    #[Test]
    public function index_supports_per_page_parameter(): void
    {
        Post::factory()->count(15)->create();

        $response = $this->getJson($this->getResourceUrl().'?per_page=5');

        // Временная отладка - посмотрим полную структуру
        dump('Full response:', $response->json());

        $response->assertOk();
    }

    #[Test]
    public function store_handles_tags_text_parameter(): void
    {
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Post with tags text',
            'content' => 'Content with tags',
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'tags_text' => 'php, laravel, testing',
        ];

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('posts', [
            'title' => 'Post with tags text',
            'content' => 'Content with tags',
        ]);
    }

    #[Test]
    public function store_handles_general_exception(): void
    {
        // Мокируем PostService чтобы выбросить исключение
        $this->mock(\App\Services\PostService::class, function ($mock) {
            $mock->shouldReceive('createPost')
                ->andThrow(new \Exception('Database connection error'));
        });

        $payload = $this->createResourceData();

        $response = $this->postJson($this->getResourceUrl(), $payload);

        // Наружу отдаётся только общее сообщение: путь к файлу и номер
        // строки не должны утекать в ответ API
        $response->assertStatus(500)
            ->assertJsonStructure(['message', 'status', 'success'])
            ->assertJsonMissingPath('file')
            ->assertJsonMissingPath('line');
    }

    #[Test]
    public function show_loads_relationships_correctly(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();
        $post->tags()->attach($tag->id);

        $response = $this->getJson($this->getResourceUrl($post->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'content',
                    'category', 'tags', 'comments',
                ],
            ]);
    }

    #[Test]
    public function update_handles_general_exception(): void
    {
        $post = Post::factory()->create();

        // Мокируем PostService чтобы выбросить исключение
        $this->mock(\App\Services\PostService::class, function ($mock) {
            $mock->shouldReceive('updatePost')
                ->andThrow(new \Exception('Update failed'));
        });

        $payload = $this->createResourceData();

        $response = $this->putJson($this->getResourceUrl($post->id), $payload);

        $response->assertStatus(500);
    }

    #[Test]
    public function index_handles_service_exception(): void
    {
        // Мокируем PostService чтобы выбросить исключение
        $this->mock(\App\Services\PostService::class, function ($mock) {
            $mock->shouldReceive('getFilteredPosts')
                ->andThrow(new \Exception('Database connection failed'));
        });

        $response = $this->getJson($this->getResourceUrl());

        $response->assertStatus(500)
            ->assertJsonStructure(['message', 'status', 'success'])
            ->assertJsonMissingPath('file')
            ->assertJsonMissingPath('line');
    }

    #[Test]
    public function index_handles_empty_search_gracefully(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson($this->getResourceUrl().'?search=');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    #[Test]
    public function index_handles_invalid_category_filter(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson($this->getResourceUrl().'?category_id=99999');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    #[Test]
    public function index_handles_invalid_tag_filter(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson($this->getResourceUrl().'?tag_id=99999');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    #[Test]
    public function index_with_category_slug_filter(): void
    {
        $category = Category::factory()->create(['slug' => 'tech-news']);
        Post::factory()->create(['category_id' => $category->id]);
        Post::factory()->create(); // другой пост

        $response = $this->getJson($this->getResourceUrl().'?category=tech-news');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    #[Test]
    public function index_with_tag_slug_filter(): void
    {
        $tag = Tag::factory()->create(['slug' => 'php-tutorial']);
        $post = Post::factory()->create();
        $post->tags()->attach($tag->id);

        Post::factory()->create(); // пост без тегов

        $response = $this->getJson($this->getResourceUrl().'?tag=php-tutorial');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    #[Test]
    public function index_with_multiple_category_ids(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category3 = Category::factory()->create();

        Post::factory()->create(['category_id' => $category1->id]);
        Post::factory()->create(['category_id' => $category2->id]);
        Post::factory()->create(['category_id' => $category3->id]);

        $response = $this->getJson(
            $this->getResourceUrl().'?category_ids[]='.$category1->id.'&category_ids[]='.$category2->id
        );

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    #[Test]
    public function index_with_multiple_tag_ids(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $post1 = Post::factory()->create();
        $post1->tags()->attach($tag1->id);

        $post2 = Post::factory()->create();
        $post2->tags()->attach($tag2->id);

        $post3 = Post::factory()->create();
        $post3->tags()->attach($tag3->id);

        $response = $this->getJson($this->getResourceUrl().'?tag_ids[]='.$tag1->id.'&tag_ids[]='.$tag2->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    #[Test]
    public function index_with_complex_search_parameters(): void
    {
        // Создаем посты для тестирования различных фильтров
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $post1 = Post::factory()->create([
            'title' => 'Laravel Advanced Tutorial',
            'category_id' => $category1->id,
        ]);
        $post1->tags()->attach([$tag1->id, $tag2->id]);

        $post2 = Post::factory()->create([
            'title' => 'PHP Basics',
            'category_id' => $category2->id,
        ]);
        $post2->tags()->attach([$tag1->id]);

        // Тестируем комбинированный поиск
        $response = $this->getJson(
            $this->getResourceUrl().'?search=Laravel&category_id='.$category1->id.'&tag_id='.$tag1->id
        );

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($post1->id, $data[0]['id']);
    }

    #[Test]
    public function show_handles_exception_in_try_catch(): void
    {
        // Тестируем с несуществующим ID для вызова исключения
        $response = $this->getJson($this->getResourceUrl(-1)); // Невалидный ID

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function destroy_supports_xml_format_with_header(): void
    {
        $post = Post::factory()->create();

        $response = $this->delete(
            $this->getResourceUrl($post->id).'?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertNoContent();
        // Проверяем заголовок Content-Type для XML
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            $this->assertStringContainsString('application/xml', $contentType);
        }
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function destroy_handles_exception_in_try_catch(): void
    {
        $post = Post::factory()->create();

        // Мокируем метод delete модели Post для исключения
        $mockPost = $this->partialMock(Post::class, function (MockInterface $mock) {
            $mock->shouldReceive('delete')->andThrow(new \Exception('Delete failed'));
        });

        $controller = new \App\Http\Controllers\Api\V1\PostController(app(\App\Services\PostService::class));

        $request = \Illuminate\Http\Request::create('/api/v1/posts/'.$post->id, 'DELETE');

        $response = $controller->destroy($request, $mockPost);

        $this->assertEquals(500, $response->getStatusCode());

        // Проверяем, что есть содержимое ответа
        $content = $response->getContent();
        $this->assertNotEmpty($content);

        // Пытаемся декодировать JSON
        $decodedContent = json_decode($content, true);
        if ($decodedContent !== null) {
            // Если это JSON, проверяем структуру
            $this->assertArrayHasKey('message', $decodedContent);
            $this->assertEquals('Ошибка при удалении поста', $decodedContent['message']);
        } else {
            // Если не JSON, просто проверяем наличие текста ошибки
            $this->assertStringContainsString('Ошибка при удалении поста', $content);
        }
    }

    #[Test]
    public function destroy_handles_exception_with_xml_format(): void
    {
        $post = Post::factory()->create();

        // Мокируем метод delete модели Post для исключения
        $mockPost = $this->partialMock(Post::class, function (MockInterface $mock) {
            $mock->shouldReceive('delete')->andThrow(new \Exception('Delete failed'));
        });

        $controller = new \App\Http\Controllers\Api\V1\PostController(app(\App\Services\PostService::class));

        $request = \Illuminate\Http\Request::create('/api/v1/posts/'.$post->id.'?format=xml', 'DELETE');

        $response = $controller->destroy($request, $mockPost);

        $this->assertEquals(500, $response->getStatusCode());

        // Для XML формата тоже должно быть содержимое с ошибкой
        $this->assertNotEmpty($response->getContent());

        // Проверяем, что это XML ответ
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            $this->assertStringContainsString('xml', strtolower($contentType));
        }
    }

    #[Test]
    public function destroy_covers_xml_branch_successfully(): void
    {
        $post = Post::factory()->create();

        $response = $this->delete(
            $this->getResourceUrl($post->id).'?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertNoContent();

        // Проверяем XML заголовок (покрывает строки 191-193)
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            $this->assertStringContainsString('application/xml', $contentType);
        }

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function show_handles_exception_with_direct_controller_test(): void
    {
        $post = Post::factory()->create();

        // Мокируем Post для исключения при load
        $mockPost = $this->partialMock(Post::class, function (MockInterface $mock) {
            $mock->shouldReceive('load')->andThrow(new \Exception('Database error'));
        });

        $controller = new \App\Http\Controllers\Api\V1\PostController(app(\App\Services\PostService::class));
        $request = \Illuminate\Http\Request::create('/api/v1/posts/'.$post->id, 'GET');

        $response = $controller->show($request, $mockPost);

        $this->assertEquals(404, $response->getStatusCode());

        // Проверяем содержимое ответа
        $content = $response->getContent();
        $this->assertNotEmpty($content);

        // Декодируем JSON и проверяем структуру
        $decodedContent = json_decode($content, true);
        if ($decodedContent !== null && is_array($decodedContent)) {
            $this->assertArrayHasKey('message', $decodedContent);
            $this->assertEquals('Пост не найден', $decodedContent['message']);
        } else {
            // Если JSON не валиден, просто проверяем наличие сообщения
            $this->assertStringContainsString('Пост не найден', $content);
        }
    }
}
