<?php

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PostService $postService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postService = new PostService();
    }

    // === Тесты для getFilteredPosts ===

    public function test_get_filtered_posts_returns_paginated_results(): void
    {
        // Создаем тестовые данные
        $category = Category::factory()->create();
        Post::factory()->count(15)->create(['category_id' => $category->id]);

        $request = new Request();
        $result = $this->postService->getFilteredPosts($request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
    }

    public function test_get_filtered_posts_filters_by_search_title(): void
    {
        $category = Category::factory()->create();
        Post::factory()->create(['title' => 'Laravel Tutorial', 'category_id' => $category->id]);
        Post::factory()->create(['title' => 'PHP Basics', 'category_id' => $category->id]);

        $request = new Request(['search_title' => 'Laravel']);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Laravel Tutorial', $result->first()->title);
    }

    public function test_get_filtered_posts_filters_by_search(): void
    {
        $category = Category::factory()->create();
        Post::factory()->create(['title' => 'Laravel Tutorial', 'category_id' => $category->id]);
        Post::factory()->create(['title' => 'PHP Basics', 'category_id' => $category->id]);

        $request = new Request(['search' => 'PHP']);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('PHP Basics', $result->first()->title);
    }

    public function test_get_filtered_posts_filters_by_single_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        Post::factory()->create(['category_id' => $category1->id]);
        Post::factory()->create(['category_id' => $category2->id]);

        $request = new Request(['category_id' => $category1->id]);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($category1->id, $result->first()->category_id);
    }

    public function test_get_filtered_posts_filters_by_multiple_categories(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category3 = Category::factory()->create();
        Post::factory()->create(['category_id' => $category1->id]);
        Post::factory()->create(['category_id' => $category2->id]);
        Post::factory()->create(['category_id' => $category3->id]);

        $request = new Request(['category_ids' => [$category1->id, $category2->id]]);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(2, $result->total());
    }

    public function test_get_filtered_posts_filters_by_single_tag(): void
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        
        $post1 = Post::factory()->create(['category_id' => $category->id]);
        $post2 = Post::factory()->create(['category_id' => $category->id]);
        
        $post1->tags()->attach($tag1->id);
        $post2->tags()->attach($tag2->id);

        $request = new Request(['tag_id' => $tag1->id]);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->first()->tags->contains($tag1));
    }

    public function test_get_filtered_posts_filters_by_multiple_tags(): void
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        $tag3 = Tag::factory()->create(['name' => 'unique-tag-3']);
        
        $post1 = Post::factory()->create(['category_id' => $category->id]);
        $post2 = Post::factory()->create(['category_id' => $category->id]);
        $post3 = Post::factory()->create(['category_id' => $category->id]);
        
        $post1->tags()->attach($tag1->id);
        $post2->tags()->attach($tag2->id);
        $post3->tags()->attach($tag3->id);

        $request = new Request(['tag_ids' => [$tag1->id, $tag2->id]]);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(2, $result->total());
    }

    public function test_get_filtered_posts_combines_multiple_filters(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        
        // Пост, который соответствует всем фильтрам
        $post1 = Post::factory()->create(['title' => 'Laravel Tutorial', 'category_id' => $category1->id]);
        $post1->tags()->attach($tag1->id);
        
        // Посты, которые не соответствуют фильтрам
        $post2 = Post::factory()->create(['title' => 'PHP Basics', 'category_id' => $category1->id]);
        $post2->tags()->attach($tag2->id);
        
        $post3 = Post::factory()->create(['title' => 'Laravel Advanced', 'category_id' => $category2->id]);
        $post3->tags()->attach($tag1->id);

        $request = new Request([
            'search' => 'Laravel',
            'category_ids' => [$category1->id],
            'tag_ids' => [$tag1->id]
        ]);
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Laravel Tutorial', $result->first()->title);
    }

    public function test_get_filtered_posts_orders_by_date_latest(): void
    {
        $category = Category::factory()->create();
        $post1 = Post::factory()->create(['date' => '2023-01-01', 'category_id' => $category->id]);
        $post2 = Post::factory()->create(['date' => '2023-01-03', 'category_id' => $category->id]);
        $post3 = Post::factory()->create(['date' => '2023-01-02', 'category_id' => $category->id]);

        $request = new Request();
        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(3, $result->total());
        $this->assertEquals('2023-01-03', $result->first()->date->format('Y-m-d'));
        $this->assertEquals('2023-01-02', $result->get(1)->date->format('Y-m-d'));
        $this->assertEquals('2023-01-01', $result->last()->date->format('Y-m-d'));
    }

    // === Тесты для createPost ===

    public function test_create_post_creates_post_with_basic_data(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ];

        $post = $this->postService->createPost($data);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('Test content', $post->content);
        $this->assertEquals($user->id, $post->user_id);
        $this->assertEquals($category->id, $post->category_id);
        $this->assertNotNull($post->date);
    }

    public function test_create_post_attaches_tags(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'tags' => [$tag1->id, $tag2->id],
        ];

        $post = $this->postService->createPost($data);

        $this->assertTrue($post->tags->contains($tag1));
        $this->assertTrue($post->tags->contains($tag2));
        $this->assertEquals(2, $post->tags->count());
    }

    public function test_create_post_works_without_tags(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ];

        $post = $this->postService->createPost($data);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals(0, $post->tags->count());
    }

    public function test_create_post_sets_current_date(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ];

        $post = $this->postService->createPost($data);

        // Проверяем, что дата установлена и является сегодняшней
        $this->assertNotNull($post->date);
        $this->assertEquals(now()->format('Y-m-d'), $post->date->format('Y-m-d'));
    }

    public function test_create_post_overwrites_passed_date(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $oldDate = '2020-01-01';
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'date' => $oldDate, // Передаем старую дату
        ];

        $post = $this->postService->createPost($data);

        // Проверяем, что дата была перезаписана на текущую
        $this->assertNotEquals($oldDate, $post->date->format('Y-m-d'));
        $this->assertEquals(now()->format('Y-m-d'), $post->date->format('Y-m-d'));
    }

    // === Тесты для updatePost ===

    public function test_update_post_updates_basic_data(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category1->id]);
        
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'category_id' => $category2->id,
        ];

        $updatedPost = $this->postService->updatePost($post, $data);

        $this->assertEquals('Updated Title', $updatedPost->title);
        $this->assertEquals('Updated content', $updatedPost->content);
        $this->assertEquals($category2->id, $updatedPost->category_id);
    }

    public function test_update_post_syncs_tags(): void
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        $tag3 = Tag::factory()->create(['name' => 'unique-tag-3']);
        
        $post = Post::factory()->create(['category_id' => $category->id]);
        $post->tags()->attach([$tag1->id, $tag2->id]);
        
        $data = [
            'title' => 'Updated Title',
            'tags' => [$tag2->id, $tag3->id],
        ];

        $updatedPost = $this->postService->updatePost($post, $data);

        $this->assertFalse($updatedPost->tags->contains($tag1));
        $this->assertTrue($updatedPost->tags->contains($tag2));
        $this->assertTrue($updatedPost->tags->contains($tag3));
        $this->assertEquals(2, $updatedPost->tags->count());
    }

    public function test_update_post_removes_all_tags_when_empty_array(): void
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        
        $post = Post::factory()->create(['category_id' => $category->id]);
        $post->tags()->attach([$tag1->id, $tag2->id]);
        
        $data = [
            'title' => 'Updated Title',
            'tags' => [],
        ];

        $updatedPost = $this->postService->updatePost($post, $data);

        $this->assertEquals(0, $updatedPost->tags->count());
    }

    public function test_update_post_works_without_tags_parameter(): void
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);
        
        $post = Post::factory()->create(['category_id' => $category->id]);
        $post->tags()->attach([$tag1->id, $tag2->id]);
        
        $data = [
            'title' => 'Updated Title',
        ];

        $updatedPost = $this->postService->updatePost($post, $data);

        $this->assertEquals('Updated Title', $updatedPost->title);
        $this->assertEquals(0, $updatedPost->tags->count()); // Теги удаляются, если не переданы
    }
}