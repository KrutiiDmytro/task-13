<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;

class ApiWorkflowTest extends ApiTestCase
{
    #[Test]
    public function complete_blog_workflow_works(): void
    {
        // 1. Создаем категорию
        $categoryResponse = $this->postJson('/api/v1/categories', [
            'name' => 'Tech News',
            'description' => 'Technology related posts',
        ]);
        $categoryResponse->assertCreated();
        $categoryId = $categoryResponse->json('data.id');

        // 2. Создаем теги
        $tag1Response = $this->postJson('/api/v1/tags', ['name' => 'PHP']);
        $tag2Response = $this->postJson('/api/v1/tags', ['name' => 'Laravel']);
        $tag1Response->assertCreated();
        $tag2Response->assertCreated();

        // 3. Создаем пост с категорией и тегами
        $postResponse = $this->postJson('/api/v1/posts', [
            'title' => 'Laravel Best Practices',
            'content' => 'Here are some Laravel best practices...',
            'category_id' => $categoryId,
            'user_id' => $this->user->id,
            'author_name' => $this->user->name,
            'author_email' => $this->user->email,
            'tags' => ['PHP', 'Laravel'],
        ]);
        $postResponse->assertCreated();
        $postId = $postResponse->json('data.id');

        // 4. Создаем комментарий к посту
        $commentResponse = $this->postJson('/api/v1/comments', [
            'author_name' => 'John Commenter',
            'author_email' => 'john@example.com',
            'content' => 'Great post!',
            'post_id' => $postId,
        ]);
        $commentResponse->assertCreated();

        // 5. Проверяем, что все связи работают
        $postDetailsResponse = $this->getJson("/api/v1/posts/{$postId}");
        $postDetailsResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'content',
                    'category' => ['id', 'name'],
                    'tags' => [['id', 'name']],
                    'comments' => [['id', 'content']],
                ],
            ]);
    }

    #[Test]
    public function api_handles_validation_errors_consistently(): void
    {
        // Тестируем валидацию для всех эндпоинтов
        $endpoints = [
            ['POST', '/api/v1/posts', []],
            ['POST', '/api/v1/categories', []],
            ['POST', '/api/v1/comments', []],
            ['POST', '/api/v1/tags', []],
        ];

        foreach ($endpoints as [$method, $url, $data]) {
            $response = $this->json($method, $url, $data);
            $response->assertStatus(422)
                ->assertJsonStructure(['message', 'errors']);
        }
    }
}
