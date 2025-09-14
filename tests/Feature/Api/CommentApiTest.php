<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->post = Post::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id
        ]);
    }

    #[Test]
public function it_can_get_list_of_comments_in_json()
{
    Comment::factory()->count(3)->create([
        'post_id' => $this->post->id
    ]);

    $response = $this->getJson('/api/v1/comments');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'author',
                        'post_id'
                    ]
                ],
                'meta' => [
                    'total',
                    'version'
                ]
            ]);
    }
}