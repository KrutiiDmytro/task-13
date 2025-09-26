<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Tag;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PostTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_casts(): void
    {
        $post = new Post();
        
        $casts = $post->getCasts();
        
        // Проверяем, что наши кастомные касты присутствуют
        $this->assertArrayHasKey('date', $casts, 'Cast for date field should exist');
        $this->assertArrayHasKey('published_at', $casts, 'Cast for published_at field should exist');
        
        // Проверяем правильные значения кастов
        $this->assertEquals('date', $casts['date'], 'Date field should be cast to date');
        $this->assertEquals('datetime', $casts['published_at'], 'Published_at field should be cast to datetime');
        
        // Дополнительно проверим, что касты работают правильно
        $this->assertIsArray($casts, 'getCasts should return an array');
        $this->assertGreaterThanOrEqual(2, count($casts), 'Should have at least our 2 custom casts');
    }

    #[Test]
    public function casts_work_correctly_with_actual_data(): void
    {
        $post = Post::factory()->create([
            'date' => '2023-12-01',
            'published_at' => '2023-12-01 10:30:00'
        ]);
        
        // Проверяем, что касты действительно работают
        $this->assertInstanceOf(\Carbon\Carbon::class, $post->date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $post->published_at);
        
        // Проверяем правильность преобразования
        $this->assertEquals('2023-12-01', $post->date->format('Y-m-d'));
        $this->assertEquals('2023-12-01 10:30:00', $post->published_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function model_has_timestamps(): void
    {
        $post = new Post();
        
        // Проверяем, что модель использует timestamps
        $this->assertTrue($post->usesTimestamps(), 'Post model should use timestamps');
        
        // Проверяем названия полей timestamps
        $this->assertEquals('created_at', $post->getCreatedAtColumn());
        $this->assertEquals('updated_at', $post->getUpdatedAtColumn());
    }

    #[Test]
    public function timestamps_are_carbon_instances(): void
    {
        $post = Post::factory()->create();
        
        // Проверяем, что timestamps являются Carbon экземплярами
        $this->assertInstanceOf(\Carbon\Carbon::class, $post->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $post->updated_at);
    }

    #[Test]
    public function it_auto_generates_slug_from_title_when_creating(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post = Post::create([
            'title' => 'Test Post Title',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $this->assertEquals('test-post-title', $post->slug);
    }

    #[Test]
    public function it_does_not_override_manually_set_slug(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post = Post::create([
            'title' => 'Test Post Title',
            'slug' => 'custom-slug',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $this->assertEquals('custom-slug', $post->slug);
    }

    #[Test]
    public function it_handles_duplicate_slugs_by_adding_counter(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        // Создаем первый пост
        $post1 = Post::create([
            'title' => 'Same Title',
            'content' => 'Test content 1',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Создаем второй пост с тем же заголовком
        $post2 = Post::create([
            'title' => 'Same Title',
            'content' => 'Test content 2',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Создаем третий пост с тем же заголовком
        $post3 = Post::create([
            'title' => 'Same Title',
            'content' => 'Test content 3',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $this->assertEquals('same-title', $post1->slug);
        $this->assertEquals('same-title-1', $post2->slug);
        $this->assertEquals('same-title-2', $post3->slug);
    }

    #[Test]
    public function it_handles_empty_slug_correctly(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post = Post::create([
            'title' => 'Title With Spaces And Numbers 123',
            'slug' => '', // Пустой slug
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $this->assertEquals('title-with-spaces-and-numbers-123', $post->slug);
    }


    #[Test]
    public function it_handles_duplicate_slugs_when_updating_title(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        // Создаем первый пост
        $post1 = Post::create([
            'title' => 'Original Title',
            'content' => 'Test content 1',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Создаем второй пост
        $post2 = Post::create([
            'title' => 'Another Title',
            'content' => 'Test content 2',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Обновляем второй пост, меняя title на такой же как у первого и очищая slug
        $post2->slug = ''; // Очищаем slug чтобы сработала логика
        $post2->title = 'Original Title'; // Устанавливаем такой же title
        $post2->save();
        
        // Проверяем, что slug автоматически изменился для избежания дублирования
        $this->assertEquals('original-title-1', $post2->fresh()->slug);
        $this->assertEquals('original-title', $post1->fresh()->slug); // первый пост не изменился
    }

    #[Test]
    public function it_handles_multiple_duplicate_slugs_when_updating_title(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        // Создаем два поста с одинаковыми title
        $post1 = Post::create([
            'title' => 'Same Title',
            'content' => 'Test content 1',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $post2 = Post::create([
            'title' => 'Same Title',
            'content' => 'Test content 2',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Создаем третий пост с другим title
        $post3 = Post::create([
            'title' => 'Different Title',
            'content' => 'Test content 3',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Обновляем третий пост, меняя title и очищая slug
        $post3->slug = '';
        $post3->title = 'Same Title';
        $post3->save();
        
        // Проверяем, что получился уникальный slug
        $this->assertEquals('same-title-2', $post3->fresh()->slug);
    }

    #[Test]
    public function it_does_not_change_slug_when_title_not_changed(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post = Post::create([
            'title' => 'Test Title',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $originalSlug = $post->slug;
        
        // Обновляем пост, не меняя title
        $post->update([
            'content' => 'Updated content'
        ]);
        
        // Slug не должен измениться
        $this->assertEquals($originalSlug, $post->fresh()->slug);
    }


    #[Test]
    public function it_regenerates_slug_when_title_changed_and_slug_empty(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post = Post::create([
            'title' => 'Original Title',
            'content' => 'Test content',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Обновляем title и очищаем slug
        $post->title = 'New Updated Title';
        $post->slug = ''; // Важно! Логика срабатывает только когда slug пустой
        $post->save();
        
        $this->assertEquals('new-updated-title', $post->fresh()->slug);
    }
}