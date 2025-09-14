<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function it_can_get_list_of_posts_in_json()
    {
        Post::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'content',
                            'category_id',
                            'user_id'
                        ]
                    ],
                    'meta'
                ]);
    }

    #[Test]
    public function it_can_get_list_of_posts_in_xml()
    {
        Post::factory()->create([
            'title' => 'Test Post XML',
            'content' => 'Test Content',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com'
    ]);

    $response = $this->get('/api/v1/posts?format=xml');

    $response->assertStatus(200)
            ->assertHeader('content-type', 'application/xml');
    
    $this->assertStringContainsString('Test Post XML', $response->getContent());
}


    #[Test]
    public function it_can_create_new_post()
    {
        $postData = [
            'title' => 'New Post',
            'content' => 'Post content',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson('/api/v1/posts', $postData);

        $response->assertStatus(201)
                ->assertJsonPath('data.title', 'New Post')
                ->assertJsonPath('data.content', 'Post content')
                ->assertJsonPath('data.category_id', $this->category->id);

        $this->assertDatabaseHas('posts', [
            'title' => 'New Post',
            'content' => 'Post content',
            'category_id' => $this->category->id
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_post()
    {
        $response = $this->postJson('/api/v1/posts', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'content', 'category_id']);
    }

    #[Test]
    public function it_validates_category_exists_when_creating_post()
    {
        $postData = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => 999 // несуществующая категория
        ];

        $response = $this->postJson('/api/v1/posts', $postData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id']);
    }

    #[Test]
    public function it_can_get_specific_post()
    {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.id', $post->id)
                ->assertJsonPath('data.title', 'Test Post')
                ->assertJsonPath('data.content', 'Test Content');
    }



    #[Test]
public function it_can_get_specific_post_in_xml_format()
{
    $post = Post::factory()->create([
        'title' => 'XML Post Single',
        'content' => 'XML Content',
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'author_name' => 'XML Author',
        'author_email' => 'xml@example.com'
    ]);

    $response = $this->get("/api/v1/posts/{$post->id}?format=xml");

    $response->assertStatus(200)
            ->assertHeader('content-type', 'application/xml');
    
    $this->assertStringContainsString('XML Post Single', $response->getContent());
}

    #[Test]
    public function it_can_update_existing_post()
    {
        $post = Post::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated content',
            'category_id' => $this->category->id
        ];

        $response = $this->putJson("/api/v1/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('data.title', 'Updated Post')
                ->assertJsonPath('data.content', 'Updated content');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post',
            'content' => 'Updated content'
        ]);
    }

    #[Test]
    public function it_can_delete_post()
    {
        $post = Post::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }

    #[Test]
    public function it_returns_404_when_post_not_found()
    {
        $response = $this->getJson('/api/v1/posts/999');

        $response->assertStatus(404);
    }
}