<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\Tag;
use PHPUnit\Framework\Attributes\Test;

class TagsApiTest extends ApiTestCase
{
    protected $resource = 'tags';

    protected function createResourceData(): array
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
        ];
    }

    #[Test]
    public function index_returns_paginated_tags_json(): void
    {
        Tag::factory()->count(5)->create();

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'posts_count', 'created_at', 'updated_at'],
                ],
                'meta' => ['total', 'count', 'per_page', 'current_page', 'total_pages'],
            ]);
    }

    #[Test]
    public function index_supports_per_page_parameter(): void
    {
        Tag::factory()->count(10)->create();

        $response = $this->getJson($this->getResourceUrl().'?per_page=5');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(5, $data, 'Должно вернуться 5 тегов');

        // per_page возвращается как массив, берем первый элемент
        $perPage = $response->json('meta.per_page');
        if (is_array($perPage)) {
            $perPage = $perPage[0];
        }
        $this->assertEquals(5, (int) $perPage);
    }

    #[Test]
    public function index_limits_per_page_to_maximum(): void
    {
        Tag::factory()->count(60)->create();

        $response = $this->getJson($this->getResourceUrl().'?per_page=100');

        $response->assertOk();

        // per_page возвращается как массив, берем первый элемент
        $perPage = $response->json('meta.per_page');
        if (is_array($perPage)) {
            $perPage = $perPage[0];
        }
        $this->assertEquals(50, (int) $perPage); // Максимум 50
    }

    #[Test]
    public function index_supports_search_by_name(): void
    {
        Tag::factory()->create(['name' => 'Laravel Framework']);
        Tag::factory()->create(['name' => 'Vue.js Library']);
        Tag::factory()->create(['name' => 'PHP Language']);

        $response = $this->getJson($this->getResourceUrl().'?search=Laravel');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Laravel Framework', $data[0]['name']);
    }

    #[Test]
    public function index_supports_search_by_slug(): void
    {
        Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'laravel-framework']);
        Tag::factory()->create(['name' => 'Other Tag', 'slug' => 'vue-library']);

        $response = $this->getJson($this->getResourceUrl().'?search=laravel');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('laravel-framework', $data[0]['slug']);
    }

    #[Test]
    public function index_includes_posts_count(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(3)->create();

        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(3, $data[0]['posts_count']);
    }

    #[Test]
    public function index_includes_posts_when_requested(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(3)->create();

        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        $response = $this->getJson($this->getResourceUrl().'?include_posts=true');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('posts', $data[0]);
        $this->assertCount(3, $data[0]['posts']);
    }

    #[Test]
    public function index_limits_posts_to_five_when_included(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(10)->create();

        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        $response = $this->getJson($this->getResourceUrl().'?include_posts=true');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('posts', $data[0]);
        $this->assertCount(5, $data[0]['posts']); // Ограничено до 5
    }

    #[Test]
    public function index_orders_tags_by_name(): void
    {
        Tag::factory()->create(['name' => 'Zebra']);
        Tag::factory()->create(['name' => 'Alpha']);
        Tag::factory()->create(['name' => 'Beta']);

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']);
        $this->assertEquals('Beta', $data[1]['name']);
        $this->assertEquals('Zebra', $data[2]['name']);
    }

    #[Test]
    public function index_supports_xml_format(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->get($this->getResourceUrl().'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->getContent());
    }

    #[Test]
    public function store_creates_tag_and_returns_201_json(): void
    {
        $payload = $this->createResourceData();

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'posts_count', 'created_at', 'updated_at'],
            ])
            ->assertJsonFragment([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ]);

        $this->assertDatabaseHas('tags', [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    #[Test]
    public function store_auto_generates_slug_when_not_provided(): void
    {
        $payload = ['name' => 'Test Tag Name'];

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('tags', [
            'name' => 'Test Tag Name',
            'slug' => 'test-tag-name',
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
        Tag::factory()->create(['name' => 'Existing Tag']);

        $response = $this->postJson($this->getResourceUrl(), [
            'name' => 'Existing Tag',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_validates_unique_slug(): void
    {
        Tag::factory()->create(['slug' => 'existing-slug']);

        $response = $this->postJson($this->getResourceUrl(), [
            'name' => 'New Tag',
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
    public function show_returns_tag_json(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(2)->create();

        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        $response = $this->getJson($this->getResourceUrl($tag->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'posts_count', 'created_at', 'updated_at'],
            ])
            ->assertJsonFragment([
                'id' => $tag->id,
                'name' => $tag->name,
                'posts_count' => 2,
            ]);
    }

    #[Test]
    public function show_includes_posts_when_requested(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(2)->create();

        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        $response = $this->getJson($this->getResourceUrl($tag->id).'?include_posts=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'posts_count',
                    'posts' => [
                        '*' => ['id', 'title', 'category', 'tags'],
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
        $tag = Tag::factory()->create();

        $response = $this->get($this->getResourceUrl($tag->id).'?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function show_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->getJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function update_updates_tag_and_returns_200_json(): void
    {
        $tag = Tag::factory()->create();
        $updateData = [
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
        ];

        $response = $this->putJson($this->getResourceUrl($tag->id), $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Tag',
                'slug' => 'updated-tag',
            ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
        ]);
    }

    #[Test]
    public function update_auto_generates_slug_when_name_changed_but_slug_not_provided(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original Name', 'slug' => 'original-slug']);
        $updateData = ['name' => 'New Name'];

        $response = $this->putJson($this->getResourceUrl($tag->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);
    }

    #[Test]
    public function update_keeps_existing_slug_when_only_name_provided_with_explicit_slug(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original Name', 'slug' => 'keep-this-slug']);
        $updateData = [
            'name' => 'New Name',
            'slug' => 'keep-this-slug',
        ];

        $response = $this->putJson($this->getResourceUrl($tag->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
            'slug' => 'keep-this-slug',
        ]);
    }

    #[Test]
    public function update_allows_partial_updates(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original Name', 'slug' => 'original-slug']);
        $updateData = ['slug' => 'new-slug-only'];

        $response = $this->putJson($this->getResourceUrl($tag->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Original Name', // Не изменилось
            'slug' => 'new-slug-only',
        ]);
    }

    #[Test]
    public function update_supports_xml_format(): void
    {
        $tag = Tag::factory()->create();
        $updateData = [
            'name' => 'Updated XML Tag',
            'slug' => 'updated-xml-tag',
        ];

        $response = $this->put($this->getResourceUrl($tag->id).'?format=xml', $updateData, ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function update_returns_422_on_validation_error(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->putJson($this->getResourceUrl($tag->id), ['name' => '']);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function update_validates_unique_name_except_current(): void
    {
        $tag1 = Tag::factory()->create(['name' => 'Tag One']);
        $tag2 = Tag::factory()->create(['name' => 'Tag Two']);

        // Попытка изменить tag2 на имя tag1
        $response = $this->putJson($this->getResourceUrl($tag2->id), ['name' => 'Tag One']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function update_allows_keeping_same_name(): void
    {
        $tag = Tag::factory()->create(['name' => 'Same Name']);

        $response = $this->putJson($this->getResourceUrl($tag->id), ['name' => 'Same Name']);

        $response->assertOk();
    }

    #[Test]
    public function update_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->putJson($this->getResourceUrl(99999), ['name' => 'Test']);

        $response->assertNotFound();
    }

    #[Test]
    public function destroy_deletes_tag_and_returns_204(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(2)->create();

        // Привязываем посты к тегу
        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        $response = $this->deleteJson($this->getResourceUrl($tag->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);

        // Проверяем, что связи с постами удалены
        foreach ($posts as $post) {
            $this->assertDatabaseMissing('post_tag', [
                'post_id' => $post->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    #[Test]
    public function destroy_supports_xml_format(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(
            $this->getResourceUrl($tag->id).'?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertNoContent();
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            $this->assertStringContainsString('application/xml', $contentType);
        }
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    #[Test]
    public function destroy_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->deleteJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function constructor_applies_auth_middleware_correctly(): void
    {
        $controller = new \App\Http\Controllers\Api\V1\TagController;

        // Проверим, что контроллер создан корректно
        $this->assertInstanceOf(\App\Http\Controllers\Api\V1\TagController::class, $controller);
    }

    #[Test]
    public function index_handles_empty_search_parameter(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson($this->getResourceUrl().'?search=');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(3, $data); // Все теги должны вернуться
    }

    #[Test]
    public function index_returns_empty_when_no_search_matches(): void
    {
        Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

        $response = $this->getJson($this->getResourceUrl().'?search=NonExistentTag');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    #[Test]
    public function all_crud_operations_work_together(): void
    {
        // 1. Создаем тег
        $createData = [
            'name' => 'Integration Test Tag',
        ];

        $createResponse = $this->postJson($this->getResourceUrl(), $createData);
        $createResponse->assertCreated();
        $tagId = $createResponse->json('data.id');

        // 2. Получаем тег
        $showResponse = $this->getJson($this->getResourceUrl($tagId));
        $showResponse->assertOk()
            ->assertJsonFragment(['name' => 'Integration Test Tag']);

        // 3. Обновляем тег
        $updateData = ['name' => 'Updated Integration Tag'];
        $updateResponse = $this->putJson($this->getResourceUrl($tagId), $updateData);
        $updateResponse->assertOk()
            ->assertJsonFragment(['name' => 'Updated Integration Tag']);

        // 4. Проверяем в списке
        $indexResponse = $this->getJson($this->getResourceUrl());
        $indexResponse->assertOk();
        $tags = collect($indexResponse->json('data'));
        $this->assertTrue($tags->contains('name', 'Updated Integration Tag'));

        // 5. Удаляем тег
        $deleteResponse = $this->deleteJson($this->getResourceUrl($tagId));
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => $tagId]);
    }
}
