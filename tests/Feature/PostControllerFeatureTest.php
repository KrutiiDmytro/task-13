<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Post;

class PostControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_posts_index(): void
    {
        // сторінка індексу постів має віддавати 200 і правильний шаблон
        $response = $this->get('/posts');
        $response->assertStatus(200)->assertViewIs('posts.index');
    }

    /** @test */
    public function can_view_single_post(): void
    {
        //  створюємо пост і перевіряємо перегляд
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post));
        $response->assertStatus(200)->assertViewIs('posts.show')->assertViewHas('post', $post);
    }

    /** @test */
    public function can_view_create_post_form_when_authenticated(): void
    {
        //  форма створення доступна лише автентифікованим
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('posts.create'));

        $response->assertStatus(200)->assertViewIs('posts.create');
    }

    /** @test */
    public function can_create_post_when_authenticated(): void
    {
        // створення поста повертає редірект і запис зʼявляється в БД
        $user = User::factory()->create();

        $payload = [
            'title'   => 'Test Post',
            'content' => 'This is a test post content.',
        ];

        $response = $this->actingAs($user)->post(route('posts.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'title'   => 'Test Post',
            'content' => 'This is a test post content.',
        ]);
    }
}
