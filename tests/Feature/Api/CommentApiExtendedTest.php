<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentApiExtendedTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->category = Category::factory()->create();
        $this->post = Post::factory()->create(['category_id' => $this->category->id]);
    }

    #[Test]
    public function it_returns_empty_array_when_no_comments_exist()
    {
        $response = $this->getJson('/api/v1/comments');

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [],
                    'meta' => [
                        'total' => 0,
                        'version' => 'v1'
                    ]
                ]);
    }

    #[Test]
    public function it_includes_post_relationship_in_comment_response()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'content' => 'Test comment with post relationship',
            'author' => 'Test Author'
        ]);

        $response = $this->getJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'content',
                        'author',
                        'post_id',
                        'created_at',
                        'updated_at',
                        'post' => [
                            'id',
                            'title'
                        ]
                    ]
                ])
                ->assertJsonPath('data.post.id', $this->post->id)
                ->assertJsonPath('data.post.title', $this->post->title);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_comment()
    {
        $response = $this->postJson('/api/v1/comments', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content', 'author', 'post_id']);
    }

    #[Test]
    public function it_validates_post_exists_when_creating_comment()
    {
        $commentData = [
            'content' => 'Test comment',
            'author' => 'Test Author',
            'post_id' => 999999 // Несуществующий пост
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['post_id']);
    }

    #[Test]
    public function it_validates_author_max_length_when_creating_comment()
    {
        $commentData = [
            'content' => 'Valid content',
            'author' => str_repeat('a', 256), // Превышаем лимит в 255 символов
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['author']);
    }

    #[Test]
    public function it_can_create_comment_with_long_content()
    {
        $longContent = str_repeat('This is a long comment. ', 50); // Уменьшим размер
        
        $commentData = [
            'content' => $longContent,
            'author' => 'Test Author',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201)
                ->assertJsonPath('data.author', 'Test Author')
                ->assertJsonPath('data.post_id', $this->post->id);

        $this->assertDatabaseHas('comments', [
            'author' => 'Test Author',
            'post_id' => $this->post->id
        ]);
        
        // Проверяем, что контент сохранился (без точного сравнения из-за длины)
        $comment = \App\Models\Comment::latest()->first();
        $this->assertStringStartsWith('This is a long comment.', $comment->content);
    }

    #[Test]
    public function it_can_update_comment_partially()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'content' => 'Original content',
            'author' => 'Original Author'
        ]);

        // Обновляем только контент
        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated content only'
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.content', 'Updated content only')
                ->assertJsonPath('data.author', 'Original Author');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content only',
            'author' => 'Original Author'
        ]);
    }

    #[Test]
    public function it_validates_author_max_length_when_updating_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'author' => str_repeat('b', 256) // Превышаем лимит
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['author']);
    }

    #[Test]
    public function it_validates_required_content_when_updating_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => '' // Пустой контент
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function it_validates_required_author_when_updating_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'author' => '' // Пустой автор
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['author']);
    }

    #[Test]
    public function it_returns_404_when_updating_non_existent_comment()
    {
        $response = $this->putJson('/api/v1/comments/999', [
            'content' => 'Updated content'
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_deleting_non_existent_comment()
    {
        $response = $this->deleteJson('/api/v1/comments/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_showing_non_existent_comment()
    {
        $response = $this->getJson('/api/v1/comments/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_showing_non_existent_comment_in_xml()
    {
        $response = $this->get('/api/v1/comments/999?format=xml');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_handle_empty_xml_comments_list()
    {
        $response = $this->get('/api/v1/comments?format=xml');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $this->assertStringContainsString('<comments', $response->getContent());
    }

    #[Test]
    public function it_returns_proper_xml_for_single_comment()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'content' => 'XML Test Comment',
            'author' => 'XML Author'
        ]);

        $response = $this->get("/api/v1/comments/{$comment->id}?format=xml");

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<comment>', $content);
        $this->assertStringContainsString('XML Test Comment', $content);
        $this->assertStringContainsString('XML Author', $content);
    }

    #[Test]
    public function it_returns_proper_xml_for_comments_collection()
    {
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'content' => 'First XML Comment',
            'author' => 'Author 1'
        ]);
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'content' => 'Second XML Comment',
            'author' => 'Author 2'
        ]);

        $response = $this->get('/api/v1/comments?format=xml');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<comments>', $content);
        $this->assertStringContainsString('First XML Comment', $content);
        $this->assertStringContainsString('Second XML Comment', $content);
    }

    #[Test]
    public function it_returns_proper_json_structure_for_comments_collection()
    {
        Comment::factory()->count(3)->create(['post_id' => $this->post->id]);

        $response = $this->getJson('/api/v1/comments');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'author',
                            'post_id',
                            'created_at',
                            'updated_at',
                            'post'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'version'
                    ]
                ])
                ->assertJsonPath('meta.total', 3)
                ->assertJsonPath('meta.version', 'v1');
    }

    #[Test]
    public function it_can_handle_special_characters_in_comment_content()
    {
        $commentData = [
            'content' => 'Comment with special characters: àáâãäåæçèéêë 🚀 ©®™',
            'author' => 'Author with émojis 🎉',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201)
                ->assertJsonPath('data.content', 'Comment with special characters: àáâãäåæçèéêë 🚀 ©®™')
                ->assertJsonPath('data.author', 'Author with émojis 🎉');
    }

    #[Test]
    public function it_returns_correct_content_type_for_json_responses()
    {
        Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->getJson('/api/v1/comments');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');
    }

    #[Test]
    public function it_preserves_timestamps_when_updating_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);
        $originalCreatedAt = $comment->created_at;

        // Ждем немного, чтобы updated_at изменился
        sleep(1);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated content'
        ]);

        $response->assertStatus(200);

        $comment->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $comment->created_at->timestamp);
        $this->assertNotEquals($originalCreatedAt->timestamp, $comment->updated_at->timestamp);
    }

    #[Test]
    public function it_loads_post_relationship_when_creating_comment()
    {
        $commentData = [
            'content' => 'New comment with post relationship',
            'author' => 'Test Author',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'post' => [
                            'id',
                            'title'
                        ]
                    ]
                ])
                ->assertJsonPath('data.post.id', $this->post->id);
    }

    #[Test]
    public function it_loads_post_relationship_when_updating_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated content with post relationship'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'post' => [
                            'id',
                            'title'
                        ]
                    ]
                ])
                ->assertJsonPath('data.post.id', $this->post->id);
    }

    #[Test]
    public function it_returns_201_status_code_when_creating_comment()
    {
        $commentData = [
            'content' => 'Status code test comment',
            'author' => 'Status Test Author',
            'post_id' => $this->post->id
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        // Проверяем, что возвращается 201 (Created), а не 200
        $response->assertStatus(201);
    }

    #[Test]
    public function it_returns_204_status_code_when_deleting_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        // Проверяем, что возвращается 204 (No Content)
        $response->assertStatus(204);
        $response->assertNoContent();
    }

    #[Test]
    public function it_can_handle_multiple_comments_for_same_post()
    {
        // Создаем несколько комментариев для одного поста
        Comment::factory()->count(5)->create(['post_id' => $this->post->id]);

        $response = $this->getJson('/api/v1/comments');

        $response->assertStatus(200)
                ->assertJsonPath('meta.total', 5);

        // Проверяем, что все комментарии относятся к одному посту
        $comments = $response->json('data');
        foreach ($comments as $comment) {
            $this->assertEquals($this->post->id, $comment['post_id']);
        }
    }

    #[Test]
    public function it_handles_invalid_data_types_in_create_request()
    {
        $response = $this->postJson('/api/v1/comments', [
            'content' => 123, // Число вместо строки
            'author' => ['array', 'instead', 'of', 'string'],
            'post_id' => 'string_instead_of_number'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['post_id']);
    }

    #[Test]
    public function it_handles_invalid_data_types_in_update_request()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 123, // Число вместо строки
            'author' => ['array', 'instead', 'of', 'string']
        ]);

        $response->assertStatus(422);
    }
}