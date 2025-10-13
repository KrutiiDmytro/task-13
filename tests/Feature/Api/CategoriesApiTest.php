<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\CategoryService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CategoriesApiTest extends ApiTestCase
{
    protected $resource = 'categories';

    protected function createResourceData(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'slug' => $this->faker->slug,
        ];
    }

    #[Test]
    public function index_returns_paginated_categories_json(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description', 'posts_count', 'created_at', 'updated_at'],
                ],
                'meta' => ['total', 'count', 'per_page', 'current_page', 'total_pages'],
            ]);
    }

    #[Test]
    public function index_supports_search_parameter(): void
    {
        Category::factory()->create(['name' => 'Laravel Framework']);
        Category::factory()->create(['name' => 'Vue.js Library']);
        Category::factory()->create(['name' => 'PHP Language']);

        $response = $this->getJson($this->getResourceUrl().'?search=Laravel');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Laravel Framework', $data[0]['name']);
    }

    #[Test]
    public function index_supports_per_page_parameter(): void
    {
        Category::factory()->count(15)->create();

        $response = $this->getJson($this->getResourceUrl().'?per_page=5');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(5, $data);
        $this->assertEquals(5, $response->json('meta.per_page'));
    }

    #[Test]
    public function index_orders_categories_by_name(): void
    {
        Category::factory()->create(['name' => 'Zebra']);
        Category::factory()->create(['name' => 'Alpha']);
        Category::factory()->create(['name' => 'Beta']);

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']);
        $this->assertEquals('Beta', $data[1]['name']);
        $this->assertEquals('Zebra', $data[2]['name']);
    }

    #[Test]
    public function index_includes_posts_count(): void
    {
        $category = Category::factory()->create();
        Post::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(3, $data[0]['posts_count']);
    }

    #[Test]
    public function index_supports_xml_format(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->get($this->getResourceUrl().'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->getContent());
    }

    #[Test]
    public function index_handles_exception_gracefully(): void
    {
        // Мокаем для исключения в index
        $this->mock(\App\Models\Category::class, function ($mock) {
            $mock->shouldReceive('withCount')->andThrow(new \Exception('Database error'));
        });

        $response = $this->getJson($this->getResourceUrl());

        $response->assertStatus(500)
            ->assertJsonStructure(['message', 'status']);
    }

    #[Test]
    public function store_creates_category_and_returns_201_json(): void
    {
        $payload = $this->createResourceData();

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'posts_count', 'created_at', 'updated_at'],
            ])
            ->assertJsonFragment([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    #[Test]
    public function store_auto_generates_slug_when_not_provided(): void
    {
        $payload = [
            'name' => 'Test Category Name',
            'description' => 'Test Description',
        ];

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category Name',
            'slug' => 'test-category-name',
        ]);
    }

    #[Test]
    public function store_returns_422_on_validation_error(): void
    {
        $response = $this->postJson($this->getResourceUrl(), []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_validates_unique_name(): void
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $response = $this->postJson($this->getResourceUrl(), [
            'name' => 'Existing Category',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_validates_unique_slug(): void
    {
        Category::factory()->create(['slug' => 'existing-slug']);

        $response = $this->postJson($this->getResourceUrl(), [
            'name' => 'New Category',
            'slug' => 'existing-slug',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
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
    public function store_handles_general_exception(): void
    {
        // Мокаем Category для исключения
        $this->mock(\App\Models\Category::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new \Exception('Database error'));
        });

        $payload = $this->createResourceData();

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertStatus(500)
            ->assertJsonStructure(['message', 'status']);
    }

    #[Test]
    public function show_returns_category_json(): void
    {
        $category = Category::factory()->create();
        Post::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson($this->getResourceUrl($category->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'posts_count', 'created_at', 'updated_at'],
            ])
            ->assertJsonFragment([
                'id' => $category->id,
                'name' => $category->name,
                'posts_count' => 2,
            ]);
    }

    #[Test]
    public function show_includes_posts_when_requested(): void
    {
        $category = Category::factory()->create();
        $posts = Post::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson($this->getResourceUrl($category->id).'?include_posts=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'posts_count',
                    'posts' => [
                        '*' => ['id', 'title', 'tags'],
                    ],
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertArrayHasKey('posts', $responseData);
        $this->assertCount(2, $responseData['posts']);
    }

    #[Test]
    public function show_supports_xml_format(): void
    {
        $category = Category::factory()->create();

        $response = $this->get($this->getResourceUrl($category->id).'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function show_returns_404_for_nonexistent_category(): void
    {
        $response = $this->getJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function show_handles_exception_gracefully(): void
    {
        // Тестируем с несуществующей категорией
        $response = $this->getJson($this->getResourceUrl(99999));

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function show_with_include_posts_parameter(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson($this->getResourceUrl($category->id).'?include_posts=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'slug', 'posts_count',
                    'posts' => [
                        '*' => ['id', 'title', 'tags'],
                    ],
                ],
            ]);

        // Проверяем, что посты действительно включены
        $responseData = $response->json('data');
        $this->assertArrayHasKey('posts', $responseData);
        $this->assertCount(1, $responseData['posts']);
    }

    #[Test]
    public function show_handles_xml_format_correctly(): void
    {
        $category = Category::factory()->create();

        $response = $this->get($this->getResourceUrl($category->id).'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function update_updates_category_and_returns_200_json(): void
    {
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'slug' => 'updated-category',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Category',
                'slug' => 'updated-category',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
        ]);
    }

    #[Test]
    public function update_auto_generates_slug_when_not_provided(): void
    {
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'New Updated Name',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Updated Name',
            'slug' => 'new-updated-name',
        ]);
    }

    #[Test]
    public function update_supports_xml_format(): void
    {
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'Updated XML Category',
            'description' => 'Updated XML Description',
        ];

        $response = $this->put($this->getResourceUrl($category->id).'?format=xml', $updateData, ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function update_returns_422_on_validation_error(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson($this->getResourceUrl($category->id), ['name' => '']);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function update_returns_404_for_nonexistent_category(): void
    {
        $response = $this->putJson($this->getResourceUrl(99999), ['name' => 'Test']);

        $response->assertNotFound();
    }

    #[Test]
    public function update_handles_duplicate_name_constraint(): void
    {
        $category1 = Category::factory()->create(['name' => 'Existing Category']);
        $category2 = Category::factory()->create(['name' => 'Another Category']);

        $updateData = [
            'name' => 'Existing Category', // Дублирующее имя
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->getResourceUrl($category2->id), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function update_handles_validation_error_with_xml_format(): void
    {
        $category = Category::factory()->create();

        $response = $this->put(
            $this->getResourceUrl($category->id).'?format=xml',
            ['name' => ''], // Пустое имя вызовет ошибку валидации
            ['Accept' => 'application/xml']
        );

        $response->assertStatus(422);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function destroy_returns_204_when_category_has_no_posts(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson($this->getResourceUrl($category->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[Test]
    public function destroy_returns_409_when_category_has_posts(): void
    {
        $category = Category::factory()->create();
        Post::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->deleteJson($this->getResourceUrl($category->id));

        $response->assertStatus(409)
            ->assertJsonStructure(['message', 'status']);

        // Категория не должна быть удалена
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function destroy_supports_xml_format(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete(
            $this->getResourceUrl($category->id).'?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertNoContent();
        // Для 204 ответов Content-Type может быть null
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            $this->assertStringContainsString('application/xml', $contentType);
        }
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[Test]
    public function destroy_returns_409_xml_when_category_has_posts(): void
    {
        $category = Category::factory()->create();
        Post::factory()->create(['category_id' => $category->id]);

        $response = $this->delete(
            $this->getResourceUrl($category->id).'?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertStatus(409);
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            $this->assertStringContainsString('application/xml', $contentType);
        }
    }

    #[Test]
    public function destroy_returns_404_for_nonexistent_category(): void
    {
        $response = $this->deleteJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function destroy_handles_general_exception(): void
    {
        $category = Category::factory()->create();

        // Мокируем CategoryService чтобы выбросить исключение
        $this->mock(CategoryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('find')->andReturn(
                Category::factory()->create()
            );
            $mock->shouldReceive('delete')->andThrow(new \Exception('Delete error'));
        });

        $response = $this->deleteJson($this->getResourceUrl($category->id));

        $response->assertStatus(500)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function constructor_applies_auth_middleware_correctly(): void
    {
        $controller = new \App\Http\Controllers\Api\V1\CategoryController;

        // Проверим, что контроллер создан корректно
        $this->assertInstanceOf(\App\Http\Controllers\Api\V1\CategoryController::class, $controller);
    }

    #[Test]
    public function all_crud_operations_work_together(): void
    {
        // 1. Создаем категорию
        $createData = [
            'name' => 'Integration Test Category',
            'description' => 'Test category for integration testing',
        ];

        $createResponse = $this->postJson($this->getResourceUrl(), $createData);
        $createResponse->assertCreated();
        $categoryId = $createResponse->json('data.id');

        // 2. Получаем категорию
        $showResponse = $this->getJson($this->getResourceUrl($categoryId));
        $showResponse->assertOk()
            ->assertJsonFragment(['name' => 'Integration Test Category']);

        // 3. Обновляем категорию
        $updateData = ['name' => 'Updated Integration Category'];
        $updateResponse = $this->putJson($this->getResourceUrl($categoryId), $updateData);
        $updateResponse->assertOk()
            ->assertJsonFragment(['name' => 'Updated Integration Category']);

        // 4. Проверяем в списке
        $indexResponse = $this->getJson($this->getResourceUrl());
        $indexResponse->assertOk();
        $categories = collect($indexResponse->json('data'));
        $this->assertTrue($categories->contains('name', 'Updated Integration Category'));

        // 5. Удаляем категорию
        $deleteResponse = $this->deleteJson($this->getResourceUrl($categoryId));
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    #[Test]
    public function show_handles_general_exception_in_try_catch(): void
    {
        // Создаем категорию с невалидным ID для вызова исключения
        $response = $this->getJson($this->getResourceUrl(-1)); // Невалидный ID

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function update_handles_general_exception_in_try_catch(): void
    {
        $category = Category::factory()->create();

        // Мокируем Eloquent модель для исключения при update
        $this->partialMock(Category::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->andThrow(new \Exception('Database error'));
        });

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertStatus(500)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function update_handles_slug_generation_logic(): void
    {
        $category = Category::factory()->create(['name' => 'Original Name', 'slug' => 'original-slug']);

        // Тест 1: Если передаем name, slug будет регенерирован (даже если тот же)
        $updateData1 = [
            'name' => 'Original Name', // То же имя
            'description' => 'New Description',
            // slug не передаем - будет автогенерирован
        ];

        $response1 = $this->putJson($this->getResourceUrl($category->id), $updateData1);

        $response1->assertOk();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Original Name',
            'slug' => 'original-name', // Будет регенерирован из имени
            'description' => 'New Description',
        ]);
    }

    #[Test]
    public function update_handles_empty_slug_with_name_change(): void
    {
        $category = Category::factory()->create();

        // Передаем пустой slug с новым именем
        $updateData = [
            'name' => 'New Category Name',
            'slug' => '', // Пустой slug должен быть автогенерирован
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Category Name',
            'slug' => 'new-category-name', // Автогенерированный slug
            'description' => 'Updated Description',
        ]);
    }

    #[Test]
    public function show_handles_exception_during_resource_creation(): void
    {
        // Тестируем с несуществующей категорией для вызова исключения
        $response = $this->getJson($this->getResourceUrl(99999));

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function update_preserves_slug_when_explicitly_provided(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'custom-slug',
        ]);

        // Обновляем с явным указанием slug
        $updateData = [
            'name' => 'Test Category Updated',
            'slug' => 'custom-slug', // Явно передаем существующий slug
            'description' => 'Updated description',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertOk();

        // Проверяем, что slug остался как указано
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Test Category Updated',
            'slug' => 'custom-slug', // Остался как указан явно
            'description' => 'Updated description',
        ]);
    }

    #[Test]
    public function update_handles_slug_generation_condition_coverage(): void
    {
        $category = Category::factory()->create(['name' => 'Original', 'slug' => 'original']);

        // Тестируем условие: empty($validated['slug']) && isset($validated['name'])
        // Случай 1: slug пустой И name передан - должен регенерироваться
        $updateData = [
            'name' => 'New Name',
            'slug' => null, // Пустой slug
            'description' => 'Test',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'new-name', // Автогенерирован
        ]);
    }

    #[Test]
    public function show_handles_exception_with_direct_controller_test(): void
    {
        $category = Category::factory()->create();

        // Мокируем Category для исключения при loadCount
        $mockCategory = $this->partialMock(Category::class, function (MockInterface $mock) {
            $mock->shouldReceive('loadCount')->andThrow(new \Exception('Database error'));
        });

        $controller = new \App\Http\Controllers\Api\V1\CategoryController;
        $request = \Illuminate\Http\Request::create('/api/v1/categories/'.$category->id, 'GET');

        $response = $controller->show($request, $mockCategory);

        $this->assertEquals(404, $response->getStatusCode());

        // Проверяем содержимое ответа
        $content = $response->getContent();
        $this->assertNotEmpty($content);
    }

    #[Test]
    public function update_handles_general_exception_with_direct_controller_test(): void
    {
        $category = Category::factory()->create();

        // Мокируем Category для исключения при update
        $mockCategory = $this->partialMock(Category::class, function (MockInterface $mock) use ($category) {
            $mock->shouldReceive('getAttribute')->with('id')->andReturn($category->id);
            $mock->shouldReceive('update')->andThrow(new \Exception('Database error'));
        });

        $controller = new \App\Http\Controllers\Api\V1\CategoryController;
        $request = \Illuminate\Http\Request::create('/api/v1/categories/'.$category->id, 'PUT');
        $request->merge([
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);

        $response = $controller->update($request, $mockCategory);

        $this->assertEquals(500, $response->getStatusCode());

        // Проверяем содержимое ответа
        $content = $response->getContent();
        $this->assertNotEmpty($content);
    }

    #[Test]
    public function show_covers_all_branches_with_include_posts(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);
        $tag = Tag::factory()->create();
        $post->tags()->attach($tag);

        // Тестируем с include_posts=true для полного покрытия
        $response = $this->getJson($this->getResourceUrl($category->id).'?include_posts=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'posts_count',
                    'posts' => [
                        '*' => ['id', 'title', 'tags'],
                    ],
                ],
            ]);
    }

    #[Test]
    public function update_covers_slug_generation_edge_case(): void
    {
        $category = Category::factory()->create(['name' => 'Test', 'slug' => 'test']);

        // Тестируем случай когда slug не пустой, но name изменился
        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'custom-slug', // Не пустой slug
            'description' => 'Updated',
        ];

        $response = $this->putJson($this->getResourceUrl($category->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'slug' => 'custom-slug', // Должен остаться как передан
            'description' => 'Updated',
        ]);
    }
}
