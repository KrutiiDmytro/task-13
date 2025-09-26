<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PostService;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $postService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postService = new PostService();
    }

    public function test_get_filtered_posts_returns_paginated_posts()
    {
        // Создаем 15 постов
        Post::factory()->count(15)->create(['date' => now()->subDays(1)]);
        $latest = Post::factory()->create(['date' => now()]);
        $request = new Request();

        $result = $this->postService->getFilteredPosts($request);

        // Проверяем пагинацию и сортировку
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(16, $result->total());
        // Проверяем, что первый пост самый новый
        $this->assertTrue($result->first()->is($latest));
    }

    public function test_get_filtered_posts_filters_by_search()
    {
        // Создаем посты с разными заголовками
        Post::factory()->create(['title' => 'Laravel Tutorial']);
        Post::factory()->create(['title' => 'PHP Guide']);
        
        $request = new Request(['search' => 'Laravel']);

        $result = $this->postService->getFilteredPosts($request);

        // Проверяем фильтрацию по поиску
        $this->assertEquals(1, $result->total());
        $this->assertStringContainsString('Laravel', $result->first()->title);
    }

    public function test_get_filtered_posts_filters_by_search_title()
    {
        // Тестируем альтернативный параметр search_title
        Post::factory()->create(['title' => 'Vue.js Tutorial']);
        Post::factory()->create(['title' => 'React Guide']);
        
        $request = new Request(['search_title' => 'Vue']);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertStringContainsString('Vue', $result->first()->title);
    }

    public function test_get_filtered_posts_filters_by_category()
    {
        // Создаем категории и посты
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Post::factory()->create(['category_id' => $category1->id]);
        Post::factory()->create(['category_id' => $category2->id]);
        
        $request = new Request(['category' => $category1->id]);

        $result = $this->postService->getFilteredPosts($request);

        // Проверяем фильтрацию по категории
        $this->assertEquals(1, $result->total());
        $this->assertEquals($category1->id, $result->first()->category_id);
    }

    public function test_get_filtered_posts_filters_by_category_id()
    {
        // Тестируем альтернативный параметр category_id
        $category = Category::factory()->create();
        
        Post::factory()->create(['category_id' => $category->id]);
        Post::factory()->create(); // без категории
        
        $request = new Request(['category_id' => $category->id]);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($category->id, $result->first()->category_id);
    }

    public function test_get_filtered_posts_filters_by_category_ids_array()
    {
        // Тестируем множественный выбор категорий
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category3 = Category::factory()->create();
        
        Post::factory()->create(['category_id' => $category1->id]);
        Post::factory()->create(['category_id' => $category2->id]);
        Post::factory()->create(['category_id' => $category3->id]);
        
        $request = new Request(['category_ids' => [$category1->id, $category2->id]]);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(2, $result->total());
        $categoryIds = $result->pluck('category_id')->toArray();
        $this->assertContains($category1->id, $categoryIds);
        $this->assertContains($category2->id, $categoryIds);
        $this->assertNotContains($category3->id, $categoryIds);
    }

    public function test_get_filtered_posts_filters_by_tag()
    {
        // Создаем теги и посты
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        
        $post1->tags()->attach($tag1);
        $post2->tags()->attach($tag2);
        
        $request = new Request(['tag' => $tag1->id]);

        $result = $this->postService->getFilteredPosts($request);

        // Проверяем фильтрацию по тегу
        $this->assertEquals(1, $result->total());
        $this->assertEquals($post1->id, $result->first()->id);
    }

    public function test_get_filtered_posts_filters_by_tag_id()
    {
        // Тестируем альтернативный параметр tag_id
        $tag = Tag::factory()->create();
        
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        
        $post1->tags()->attach($tag);
        
        $request = new Request(['tag_id' => $tag->id]);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($post1->id, $result->first()->id);
    }

    public function test_get_filtered_posts_filters_by_tag_ids_array()
    {
        // Тестируем множественный выбор тегов
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $post3 = Post::factory()->create();
        
        $post1->tags()->attach($tag1);
        $post2->tags()->attach($tag2);
        $post3->tags()->attach($tag3);
        
        $request = new Request(['tag_ids' => [$tag1->id, $tag2->id]]);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(2, $result->total());
        $postIds = $result->pluck('id')->toArray();
        $this->assertContains($post1->id, $postIds);
        $this->assertContains($post2->id, $postIds);
        $this->assertNotContains($post3->id, $postIds);
    }

    public function test_get_filtered_posts_combines_all_filters()
    {
        // Тестируем комбинацию всех фильтров
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        
        $matchingPost = Post::factory()->create([
            'title' => 'Laravel Advanced Guide',
            'category_id' => $category->id
        ]);
        $matchingPost->tags()->attach($tag);
        
        // Создаем посты, которые не должны попасть в результат
        Post::factory()->create(['title' => 'PHP Guide']); // неправильный заголовок
        $wrongCategoryPost = Post::factory()->create([
            'title' => 'Laravel Basics',
            'category_id' => Category::factory()->create()->id
        ]); // неправильная категория
        
        $request = new Request([
            'search' => 'Laravel',
            'category' => $category->id,
            'tag' => $tag->id
        ]);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->first()->is($matchingPost));
    }

    public function test_create_post_creates_post_with_tags()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $postData = [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'tags' => [$tag1->id, $tag2->id]
        ];

        $post = $this->postService->createPost($postData);

        // Проверяем создание поста с тегами
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('Test Content', $post->content);
        $this->assertNotNull($post->date);
        $this->assertTrue($post->tags->contains($tag1));
        $this->assertTrue($post->tags->contains($tag2));
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Test Post'
        ]);
    }

    public function test_create_post_without_tags()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $postData = [
            'title' => 'Test Post Without Tags',
            'content' => 'Test Content',
            'user_id' => $user->id,
            'category_id' => $category->id
        ];

        $post = $this->postService->createPost($postData);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post Without Tags', $post->title);
        $this->assertNotNull($post->date);
        $this->assertEquals(0, $post->tags->count());
    }

    public function test_update_post_updates_tags()
    {
        $post = Post::factory()->create();
        $oldTag = Tag::factory()->create();
        $newTag = Tag::factory()->create();
        
        $post->tags()->attach($oldTag);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags' => [$newTag->id]
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем обновление тегов
        $this->assertEquals('Updated Post', $updatedPost->title);
        $this->assertEquals('Updated Content', $updatedPost->content);
        $this->assertFalse($updatedPost->tags->contains($oldTag));
        $this->assertTrue($updatedPost->tags->contains($newTag));
    }

    public function test_update_post_removes_all_tags()
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();
        
        $post->tags()->attach($tag);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags' => [] // пустой массив тегов
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        $this->assertEquals('Updated Post', $updatedPost->title);
        $this->assertEquals(0, $updatedPost->tags->count());
    }

    public function test_update_post_without_tags_parameter()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);
        $post->tags()->attach($tag);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content'
            // нет параметра 'tags'
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        $this->assertEquals('Updated Post', $updatedPost->title);
        // Теги должны остаться неизменными, так как параметр 'tags' не передан
        $this->assertEquals(1, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains($tag));
    }


    public function test_get_filtered_posts_searches_in_content(): void
    {
        // Тестируем поиск по содержимому поста
        Post::factory()->create([
            'title' => 'Simple Title',
            'content' => 'This content contains Laravel framework information'
        ]);
        Post::factory()->create([
            'title' => 'Another Title', 
            'content' => 'This is about PHP programming'
        ]);
        
        $request = new Request(['search' => 'Laravel']);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertStringContainsString('Laravel', $result->first()->content);
    }

    public function test_get_filtered_posts_uses_q_parameter(): void
    {
        // Тестируем параметр 'q' для поиска
        Post::factory()->create(['title' => 'Vue.js Tutorial']);
        Post::factory()->create(['title' => 'React Guide']);
        
        $request = new Request(['q' => 'Vue']);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertStringContainsString('Vue', $result->first()->title);
    }

    public function test_get_filtered_posts_filters_by_category_slug(): void
    {
        // Тестируем фильтрацию по slug категории
        $category = Category::factory()->create(['slug' => 'technology']);
        
        Post::factory()->create(['category_id' => $category->id]);
        Post::factory()->create(); // без категории
        
        $request = new Request(['category' => 'technology']);

        $result = $this->postService->getFilteredPosts($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($category->id, $result->first()->category_id);
    }

    public function test_create_post_creates_tags_from_strings(): void
    {
        // Тестируем создание тегов из строк
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $postData = [
            'title' => 'Test Post with String Tags',
            'content' => 'Test Content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'tags' => ['New Tag', 'Another Tag'] // строки вместо ID
        ];

        $post = $this->postService->createPost($postData);

        // Проверяем, что теги созданы
        $this->assertEquals(2, $post->tags->count());
        $this->assertTrue($post->tags->contains('name', 'New Tag'));
        $this->assertTrue($post->tags->contains('name', 'Another Tag'));
        
        // Проверяем, что теги созданы в БД
        $this->assertDatabaseHas('tags', ['name' => 'New Tag']);
        $this->assertDatabaseHas('tags', ['name' => 'Another Tag']);
    }

    public function test_create_post_handles_mixed_tag_types(): void
    {
        // Тестируем смешанные типы тегов (ID и строки)
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $existingTag = Tag::factory()->create();

        $postData = [
            'title' => 'Test Post with Mixed Tags',
            'content' => 'Test Content',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'tags' => [$existingTag->id, 'Brand New Tag'] // смешанные типы
        ];

        $post = $this->postService->createPost($postData);

        // Проверяем, что оба тега присоединены
        $this->assertEquals(2, $post->tags->count());
        $this->assertTrue($post->tags->contains($existingTag));
        $this->assertTrue($post->tags->contains('name', 'Brand New Tag'));
    }

    public function test_update_post_creates_tags_from_strings(): void
    {
        // Тестируем создание тегов из строк при обновлении
        $post = Post::factory()->create();

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags' => ['Updated Tag', 'Another Updated Tag']
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем создание новых тегов
        $this->assertEquals(2, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains('name', 'Updated Tag'));
        $this->assertTrue($updatedPost->tags->contains('name', 'Another Updated Tag'));
    }


    public function test_get_filtered_posts_filters_by_tag_slug(): void
    {
        // Тестируем фильтрацию по slug тега
        $tag = Tag::factory()->create(['slug' => 'laravel-framework']);
        
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        
        $post1->tags()->attach($tag);
        
        $request = new Request(['tag' => 'laravel-framework']); // передаем slug, а не ID

        $result = $this->postService->getFilteredPosts($request);

        // Проверяем фильтрацию по slug тега
        $this->assertEquals(1, $result->total());
        $this->assertEquals($post1->id, $result->first()->id);
    }

        
    public function test_update_post_handles_tags_text_parameter(): void
    {
        // Тестируем обработку параметра tags_text
        $post = Post::factory()->create();

        $updateData = [
            'title' => 'Updated Post with Tags Text',
            'content' => 'Updated Content',
            'tags_text' => 'php, laravel, testing, web development'
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем создание тегов из строки
        $this->assertEquals(4, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains('name', 'php'));
        $this->assertTrue($updatedPost->tags->contains('name', 'laravel'));
        $this->assertTrue($updatedPost->tags->contains('name', 'testing'));
        $this->assertTrue($updatedPost->tags->contains('name', 'web development'));
        
        // Проверяем, что теги созданы в БД
        $this->assertDatabaseHas('tags', ['name' => 'php']);
        $this->assertDatabaseHas('tags', ['name' => 'laravel']);
        $this->assertDatabaseHas('tags', ['name' => 'testing']);
        $this->assertDatabaseHas('tags', ['name' => 'web development']);
    }

    public function test_update_post_handles_tags_text_with_empty_values(): void
    {
        // Тестируем обработку tags_text с пустыми значениями
        $post = Post::factory()->create();

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags_text' => 'php, , laravel, , testing' // с пустыми значениями
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем, что пустые значения игнорируются
        $this->assertEquals(3, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains('name', 'php'));
        $this->assertTrue($updatedPost->tags->contains('name', 'laravel'));
        $this->assertTrue($updatedPost->tags->contains('name', 'testing'));
    }

    public function test_update_post_handles_mixed_tag_types(): void
    {
        // Тестируем смешанные типы тегов при обновлении (ID и строки)
        $post = Post::factory()->create();
        $existingTag = Tag::factory()->create(['name' => 'Existing Tag']);

        $updateData = [
            'title' => 'Updated Post with Mixed Tags',
            'content' => 'Updated Content',
            'tags' => [$existingTag->id, 'New String Tag']
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем обработку смешанных типов
        $this->assertEquals(2, $updatedPost->tags->count()); // существующий + новый
        $this->assertTrue($updatedPost->tags->contains($existingTag));
        $this->assertTrue($updatedPost->tags->contains('name', 'New String Tag'));
    }

    public function test_update_post_handles_numeric_string_tags(): void
    {
        // Тестируем обработку числовых ID как строк
        $post = Post::factory()->create();
        $existingTag = Tag::factory()->create(['name' => 'Existing Tag']);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags' => [(string)$existingTag->id] // ID как строка
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем, что числовая строка обработана как ID
        $this->assertEquals(1, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains($existingTag));
    }

    public function test_update_post_with_both_tags_and_tags_text(): void
    {
        // Тестируем одновременную передачу tags и tags_text
        $post = Post::factory()->create();
        $existingTag = Tag::factory()->create(['name' => 'Existing Tag']);

        $updateData = [
            'title' => 'Updated Post with Both',
            'content' => 'Updated Content',
            'tags' => [$existingTag->id],
            'tags_text' => 'text-tag-1, text-tag-2'
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем, что обрабатываются оба параметра
        $this->assertEquals(3, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains($existingTag));
        $this->assertTrue($updatedPost->tags->contains('name', 'text-tag-1'));
        $this->assertTrue($updatedPost->tags->contains('name', 'text-tag-2'));
    }

    public function test_update_post_removes_duplicate_tag_ids(): void
    {
        // Тестируем удаление дубликатов тегов
        $post = Post::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags' => [$tag1->id, $tag2->id, $tag1->id, 'Tag 2'] // дубликаты
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        // Проверяем, что дубликаты удалены (должно быть только 2 уникальных тега)
        $this->assertEquals(2, $updatedPost->tags->count());
        $this->assertTrue($updatedPost->tags->contains($tag1));
        $this->assertTrue($updatedPost->tags->contains($tag2));
    }

        
    public function test_update_post_handles_non_existent_tag_ids(): void
    {
        // Тестируем обработку несуществующих ID тегов
        $post = Post::factory()->create();
        $existingTag = Tag::factory()->create(['name' => 'Existing Tag']);
        
        // Найдем ID, который точно не существует
        $nonExistentId = Tag::max('id') + 1000;

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'tags' => [$existingTag->id, $nonExistentId] // существующий + несуществующий ID
        ];

        // Ожидаем исключение или игнорирование несуществующего ID
        try {
            $updatedPost = $this->postService->updatePost($post, $updateData);
            // Если исключение не выброшено, проверяем, что только существующий тег присоединен
            $this->assertEquals(1, $updatedPost->tags->count());
            $this->assertTrue($updatedPost->tags->contains($existingTag));
        } catch (\Exception $e) {
            // Если выброшено исключение, это тоже допустимое поведение
            $this->assertInstanceOf(\Illuminate\Database\QueryException::class, $e);
        }
    }
}