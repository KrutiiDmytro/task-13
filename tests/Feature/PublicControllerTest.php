<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class PublicControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_published_posts(): void
    {
        // Создаем опубликованные посты
        $publishedPosts = Post::factory()->count(3)->create([
            'published_at' => now()->subDay(),
        ]);

        // Создаем неопубликованный пост
        $unpublishedPost = Post::factory()->create([
            'published_at' => null,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
                 ->assertViewIs('public.index')
                 ->assertViewHas('posts');

        $viewPosts = $response->viewData('posts');
        
        // Проверяем, что показаны только опубликованные посты
        $this->assertCount(3, $viewPosts->items());
        
        foreach ($publishedPosts as $post) {
            $this->assertContains($post->id, $viewPosts->pluck('id'));
        }
        
        $this->assertNotContains($unpublishedPost->id, $viewPosts->pluck('id'));
    }

    #[Test]
    public function search_finds_posts_by_term(): void
    {
        $matchingPost = Post::factory()->create([
            'title' => 'Laravel Tutorial',
            'content' => 'Learn Laravel framework',
            'published_at' => now()->subDay(),
        ]);

        $nonMatchingPost = Post::factory()->create([
            'title' => 'Vue.js Guide',
            'content' => 'Learn Vue.js',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/search?q=Laravel');

        $response->assertStatus(200)
                 ->assertViewIs('public.search')
                 ->assertViewHas(['posts', 'q']);

        $viewPosts = $response->viewData('posts');
        $this->assertEquals('Laravel', $response->viewData('q'));
        
        // Проверяем, что найден правильный пост
        $this->assertContains($matchingPost->id, $viewPosts->pluck('id'));
        $this->assertNotContains($nonMatchingPost->id, $viewPosts->pluck('id'));
    }

    #[Test]
    public function search_filters_by_category(): void
    {
        $category1 = Category::factory()->create(['slug' => 'tech']);
        $category2 = Category::factory()->create(['slug' => 'news']);

        $techPost = Post::factory()->create([
            'title' => 'Tech Article',
            'category_id' => $category1->id,
            'published_at' => now()->subDay(),
        ]);

        $newsPost = Post::factory()->create([
            'title' => 'News Article',
            'category_id' => $category2->id,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/search?q=Article&category=tech');

        $response->assertStatus(200)
                 ->assertViewIs('public.search');

        $viewPosts = $response->viewData('posts');
        
        $this->assertContains($techPost->id, $viewPosts->pluck('id'));
        $this->assertNotContains($newsPost->id, $viewPosts->pluck('id'));
    }

    #[Test]
    public function search_filters_by_tag(): void
    {
        $tag1 = Tag::factory()->create(['slug' => 'php']);
        $tag2 = Tag::factory()->create(['slug' => 'javascript']);

        $phpPost = Post::factory()->create([
            'title' => 'PHP Tutorial',
            'published_at' => now()->subDay(),
        ]);
        $phpPost->tags()->attach($tag1);

        $jsPost = Post::factory()->create([
            'title' => 'JavaScript Guide',
            'published_at' => now()->subDay(),
        ]);
        $jsPost->tags()->attach($tag2);

        $response = $this->get('/search?q=Tutorial&tag=php');

        $response->assertStatus(200)
                 ->assertViewIs('public.search');

        $viewPosts = $response->viewData('posts');
        
        $this->assertContains($phpPost->id, $viewPosts->pluck('id'));
        $this->assertNotContains($jsPost->id, $viewPosts->pluck('id'));
    }

    #[Test]
    public function by_category_displays_posts_for_category(): void
    {
        $category = Category::factory()->create(['slug' => 'technology']);
        $otherCategory = Category::factory()->create(['slug' => 'health']);

        $categoryPosts = Post::factory()->count(2)->create([
            'category_id' => $category->id,
            'published_at' => now()->subDay(),
        ]);

        $otherPost = Post::factory()->create([
            'category_id' => $otherCategory->id,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get("/category/{$category->slug}");

        $response->assertStatus(200)
                 ->assertViewIs('public.category')
                 ->assertViewHas(['category', 'posts']);

        $viewCategory = $response->viewData('category');
        $viewPosts = $response->viewData('posts');

        $this->assertTrue($viewCategory->is($category));
        $this->assertCount(2, $viewPosts->items());
        
        foreach ($categoryPosts as $post) {
            $this->assertContains($post->id, $viewPosts->pluck('id'));
        }
        
        $this->assertNotContains($otherPost->id, $viewPosts->pluck('id'));
    }

    #[Test]
    public function by_category_returns_404_for_nonexistent_category(): void
    {
        $response = $this->get('/category/nonexistent-category');

        $response->assertNotFound();
    }

    #[Test]
    public function by_tag_displays_posts_for_tag(): void
    {
        $tag = Tag::factory()->create(['slug' => 'laravel']);
        $otherTag = Tag::factory()->create(['slug' => 'vue']);

        $taggedPosts = Post::factory()->count(2)->create([
            'published_at' => now()->subDay(),
        ]);
        foreach ($taggedPosts as $post) {
            $post->tags()->attach($tag);
        }

        $otherPost = Post::factory()->create([
            'published_at' => now()->subDay(),
        ]);
        $otherPost->tags()->attach($otherTag);

        $response = $this->get("/tag/{$tag->slug}");

        $response->assertStatus(200)
                 ->assertViewIs('public.tag')
                 ->assertViewHas(['tag', 'posts']);

        $viewTag = $response->viewData('tag');
        $viewPosts = $response->viewData('posts');

        $this->assertTrue($viewTag->is($tag));
        $this->assertCount(2, $viewPosts->items());
        
        foreach ($taggedPosts as $post) {
            $this->assertContains($post->id, $viewPosts->pluck('id'));
        }
        
        $this->assertNotContains($otherPost->id, $viewPosts->pluck('id'));
    }

    #[Test]
    public function by_tag_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->get('/tag/nonexistent-tag');

        $response->assertNotFound();
    }

    #[Test]
    public function show_displays_post_by_slug(): void
    {
        $post = Post::factory()->create([
            'slug' => 'test-post-slug',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get("/post/{$post->slug}");

        $response->assertStatus(200)
                 ->assertViewIs('public.post')
                 ->assertViewHas('post');

        $viewPost = $response->viewData('post');
        $this->assertTrue($viewPost->is($post));
    }

    #[Test]
    public function show_returns_404_for_unpublished_post(): void
    {
        $post = Post::factory()->create([
            'slug' => 'unpublished-post',
            'published_at' => null,
        ]);

        $response = $this->get("/post/{$post->slug}");

        $response->assertNotFound();
    }

    #[Test]
    public function show_returns_404_for_nonexistent_post(): void
    {
        $response = $this->get('/post/nonexistent-post-slug');

        $response->assertNotFound();
    }

    #[Test]
    public function search_handles_empty_query(): void
    {
        Post::factory()->count(3)->create(['published_at' => now()->subDay()]);

        $response = $this->get('/search?q=');

        $response->assertStatus(200)
                 ->assertViewIs('public.search')
                 ->assertViewHas('posts');

        $viewPosts = $response->viewData('posts');
        $this->assertCount(3, $viewPosts->items());
    }

    #[Test]
    public function search_handles_no_results(): void
    {
        Post::factory()->create([
            'title' => 'Different Title',
            'content' => 'Different content',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/search?q=NonexistentTerm');

        $response->assertStatus(200)
                 ->assertViewIs('public.search')
                 ->assertViewHas('posts');

        $viewPosts = $response->viewData('posts');
        $this->assertCount(0, $viewPosts->items());
    }

    #[Test]
    public function index_orders_posts_by_published_date_desc(): void
    {
        $oldPost = Post::factory()->create(['published_at' => now()->subDays(3)]);
        $newerPost = Post::factory()->create(['published_at' => now()->subDays(1)]);
        $newestPost = Post::factory()->create(['published_at' => now()->subHours(1)]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $viewPosts = $response->viewData('posts');
        
        // Проверяем правильный порядок (новые сначала)
        $this->assertEquals($newestPost->id, $viewPosts->items()[0]->id);
        $this->assertEquals($newerPost->id, $viewPosts->items()[1]->id);
        $this->assertEquals($oldPost->id, $viewPosts->items()[2]->id);
    }

    #[Test]
    public function all_public_routes_are_accessible(): void
    {
        // Создаем тестовые данные
        $category = Category::factory()->create(['slug' => 'test-category']);
        $tag = Tag::factory()->create(['slug' => 'test-tag']);
        $post = Post::factory()->create([
            'slug' => 'test-post',
            'published_at' => now()->subDay(),
        ]);

        // Тестируем все публичные роуты
        $routes = [
            '/' => 200,
            '/search?q=test' => 200,
            "/category/{$category->slug}" => 200,
            "/tag/{$tag->slug}" => 200,
            "/post/{$post->slug}" => 200,
        ];

        foreach ($routes as $route => $expectedStatus) {
            $response = $this->get($route);
            $response->assertStatus($expectedStatus, "Route {$route} failed");
        }
    }
}