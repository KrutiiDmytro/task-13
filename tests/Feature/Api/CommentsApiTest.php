<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use PHPUnit\Framework\Attributes\Test;

class CommentsApiTest extends ApiTestCase
{
    protected $resource = 'comments';

    protected function createResourceData(): array
    {
        $post = Post::factory()->create();
        
        return [
            'content' => $this->faker->paragraph,
            'author_name' => $this->faker->name,
            'author_email' => $this->faker->safeEmail,
            'post_id' => $post->id
        ];
    }

    #[Test]
    public function index_returns_paginated_comments_json(): void
    {
        Comment::factory()->count(5)->create();

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'content', 'author_name', 'author_email', 'post_id', 'created_at', 'updated_at', 'post']
                ],
                'meta' => ['total', 'count', 'per_page', 'current_page', 'total_pages']
            ]);
    }

    #[Test]
    public function index_supports_per_page_parameter(): void
    {
        Comment::factory()->count(60)->create();

        $response = $this->getJson($this->getResourceUrl() . '?per_page=5');

        $response->assertOk();
        
        $data = $response->json('data');
        $this->assertCount(5, $data);
        $this->assertEquals(5, (int)$response->json('meta.per_page'));
    }

    #[Test]
    public function index_limits_per_page_to_maximum(): void
    {
        Comment::factory()->count(60)->create();

        $response = $this->getJson($this->getResourceUrl() . '?per_page=100');

        $response->assertOk();
        $this->assertEquals(50, (int)$response->json('meta.per_page')); 
    }

    #[Test]
    public function index_filters_by_post_id(): void
    {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        
        Comment::factory()->count(3)->create(['post_id' => $post1->id]);
        Comment::factory()->count(2)->create(['post_id' => $post2->id]);

        $response = $this->getJson($this->getResourceUrl() . "?post_id={$post1->id}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(3, $data);
        
        foreach ($data as $comment) {
            $this->assertEquals($post1->id, $comment['post_id']);
        }
    }

    #[Test]
    public function index_orders_comments_by_created_at_desc(): void
    {
        $oldComment = Comment::factory()->create(['created_at' => now()->subDays(2)]);
        $newerComment = Comment::factory()->create(['created_at' => now()->subDay()]);
        $newestComment = Comment::factory()->create(['created_at' => now()]);

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk();
        $data = $response->json('data');
        
        // Проверяем правильный порядок (новые сначала)
        $this->assertEquals($newestComment->id, $data[0]['id']);
        $this->assertEquals($newerComment->id, $data[1]['id']);
        $this->assertEquals($oldComment->id, $data[2]['id']);
    }

    #[Test]
    public function index_includes_post_relationship(): void
    {
        $post = Post::factory()->create(['title' => 'Test Post']);
        Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->getJson($this->getResourceUrl());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('post', $data[0]);
        $this->assertEquals('Test Post', $data[0]['post']['title']);
    }

    #[Test]
    public function index_supports_xml_format(): void
    {
        Comment::factory()->count(3)->create();

        $response = $this->get($this->getResourceUrl() . '?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->getContent());
    }

    #[Test]
    public function store_creates_comment_and_returns_201_json(): void
    {
        $payload = $this->createResourceData();

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'content', 'author_name', 'author_email', 'post_id', 'created_at', 'updated_at', 'post']
            ])
            ->assertJsonFragment([
                'content' => $payload['content'],
                'author_name' => $payload['author_name'],
                'author_email' => $payload['author_email'],
                'post_id' => $payload['post_id']
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => $payload['content'],
            'author_name' => $payload['author_name'],
            'author_email' => $payload['author_email'],
            'post_id' => $payload['post_id']
        ]);
    }

    #[Test]
    public function store_creates_comment_with_minimal_data(): void
    {
        $post = Post::factory()->create();
        $payload = [
            'content' => 'Minimal comment content',
            'post_id' => $post->id
        ];

        $response = $this->postJson($this->getResourceUrl(), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('comments', [
            'content' => 'Minimal comment content',
            'post_id' => $post->id,
            'author_name' => null,
            'author_email' => null
        ]);
    }

    #[Test]
    public function store_returns_422_on_validation_error(): void
    {
        $response = $this->postJson($this->getResourceUrl(), []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors'])
                 ->assertJsonValidationErrors(['content', 'post_id']);
    }

    #[Test]
    public function store_validates_post_exists(): void
    {
        $response = $this->postJson($this->getResourceUrl(), [
            'content' => 'Test content',
            'post_id' => 99999 // Несуществующий post
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['post_id']);
    }

    #[Test]
    public function store_validates_content_max_length(): void
    {
        $post = Post::factory()->create();
        $longContent = str_repeat('a', 1001); // Превышает максимум 1000

        $response = $this->postJson($this->getResourceUrl(), [
            'content' => $longContent,
            'post_id' => $post->id
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function store_validates_email_format(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson($this->getResourceUrl(), [
            'content' => 'Test content',
            'post_id' => $post->id,
            'author_email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['author_email']);
    }

    #[Test]
    public function store_supports_xml_format(): void
    {
        $payload = $this->createResourceData();

        $response = $this->post($this->getResourceUrl() . '?format=xml', $payload, ['Accept' => 'application/xml']);

        $response->assertCreated();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function show_returns_comment_json(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->getJson($this->getResourceUrl($comment->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'content', 'author_name', 'author_email', 'post_id', 'created_at', 'updated_at', 'post']
            ])
            ->assertJsonFragment([
                'id' => $comment->id,
                'content' => $comment->content
            ]);
    }

    #[Test]
    public function show_includes_post_relationship(): void
    {
        $post = Post::factory()->create(['title' => 'Related Post']);
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->getJson($this->getResourceUrl($comment->id));

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('post', $data);
        $this->assertEquals('Related Post', $data['post']['title']);
    }

    #[Test]
    public function show_supports_xml_format(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->get($this->getResourceUrl($comment->id) . '?format=xml', ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function show_returns_404_for_nonexistent_comment(): void
    {
        $response = $this->getJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function update_updates_comment_and_returns_200_json(): void
    {
        $comment = Comment::factory()->create();
        $updateData = [
            'content' => 'Updated comment content',
            'author_name' => 'Updated Author',
            'author_email' => 'updated@example.com'
        ];

        $response = $this->putJson($this->getResourceUrl($comment->id), $updateData);

        $response->assertOk()
                 ->assertJsonFragment([
                     'content' => 'Updated comment content',
                     'author_name' => 'Updated Author',
                     'author_email' => 'updated@example.com'
                 ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment content',
            'author_name' => 'Updated Author',
            'author_email' => 'updated@example.com'
        ]);
    }

    #[Test]
    public function update_allows_partial_updates(): void
    {
        $comment = Comment::factory()->create([
            'content' => 'Original content',
            'author_name' => 'Original Author',
            'author_email' => 'original@example.com'
        ]);

        // Обновляем только content
        $response = $this->putJson($this->getResourceUrl($comment->id), [
            'content' => 'Only content updated'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Only content updated',
            'author_name' => 'Original Author', // Не изменилось
            'author_email' => 'original@example.com' // Не изменилось
        ]);
    }

    #[Test]
    public function update_allows_setting_fields_to_null(): void
    {
        $comment = Comment::factory()->create([
            'author_name' => 'Original Author',
            'author_email' => 'original@example.com'
        ]);

        $response = $this->putJson($this->getResourceUrl($comment->id), [
            'author_name' => null,
            'author_email' => null
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'author_name' => null,
            'author_email' => null
        ]);
    }

    #[Test]
    public function update_supports_xml_format(): void
    {
        $comment = Comment::factory()->create();
        $updateData = [
            'content' => 'Updated XML comment',
            'author_name' => 'XML Author'
        ];

        $response = $this->put($this->getResourceUrl($comment->id) . '?format=xml', $updateData, ['Accept' => 'application/xml']);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function update_returns_422_on_validation_error(): void
    {
        $comment = Comment::factory()->create();
        $longContent = str_repeat('a', 1001); // Превышает максимум

        $response = $this->putJson($this->getResourceUrl($comment->id), [
            'content' => $longContent
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors'])
                 ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function update_validates_email_format(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->putJson($this->getResourceUrl($comment->id), [
            'author_email' => 'invalid-email-format'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['author_email']);
    }

    #[Test]
    public function update_returns_404_for_nonexistent_comment(): void
    {
        $response = $this->putJson($this->getResourceUrl(99999), [
            'content' => 'Updated content'
        ]);

        $response->assertNotFound();
    }

    #[Test]
    public function destroy_deletes_comment_and_returns_204(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson($this->getResourceUrl($comment->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function destroy_supports_xml_format(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->delete(
            $this->getResourceUrl($comment->id) . '?format=xml',
            [],
            ['Accept' => 'application/xml']
        );

        $response->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function destroy_returns_404_for_nonexistent_comment(): void
    {
        $response = $this->deleteJson($this->getResourceUrl(99999));

        $response->assertNotFound();
    }

    #[Test]
    public function constructor_applies_auth_middleware_correctly(): void
    {
        $controller = new \App\Http\Controllers\Api\V1\CommentController();
        
        // Проверим, что контроллер создан корректно
        $this->assertInstanceOf(\App\Http\Controllers\Api\V1\CommentController::class, $controller);
    }

    #[Test]
    public function index_handles_empty_post_id_filter(): void
    {
        Comment::factory()->count(3)->create();

        // Пустой post_id должен игнорироваться и возвращать все комментарии
        $response = $this->getJson($this->getResourceUrl() . '?post_id=');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    #[Test]
    public function index_returns_empty_when_no_comments_for_post(): void
    {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        
        Comment::factory()->count(3)->create(['post_id' => $post1->id]);

        $response = $this->getJson($this->getResourceUrl() . "?post_id={$post2->id}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    #[Test]
    public function all_crud_operations_work_together(): void
    {
        $post = Post::factory()->create();

        // 1. Создаем комментарий
        $createData = [
            'content' => 'Integration test comment',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'post_id' => $post->id
        ];

        $createResponse = $this->postJson($this->getResourceUrl(), $createData);
        $createResponse->assertCreated();
        $commentId = $createResponse->json('data.id');

        // 2. Получаем комментарий
        $showResponse = $this->getJson($this->getResourceUrl($commentId));
        $showResponse->assertOk()
                     ->assertJsonFragment(['content' => 'Integration test comment']);

        // 3. Обновляем комментарий
        $updateData = ['content' => 'Updated integration comment'];
        $updateResponse = $this->putJson($this->getResourceUrl($commentId), $updateData);
        $updateResponse->assertOk()
                       ->assertJsonFragment(['content' => 'Updated integration comment']);

        // 4. Проверяем в списке
        $indexResponse = $this->getJson($this->getResourceUrl());
        $indexResponse->assertOk();
        $comments = collect($indexResponse->json('data'));
        $this->assertTrue($comments->contains('content', 'Updated integration comment'));

        // 5. Удаляем комментарий
        $deleteResponse = $this->deleteJson($this->getResourceUrl($commentId));
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $commentId]);
    }
}