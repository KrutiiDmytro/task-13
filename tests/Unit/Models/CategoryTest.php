<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_category_with_auto_generated_slug(): void
    {
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
        $this->assertEquals('Test Description', $category->description);
    }

    #[Test]
    public function it_can_create_category_with_custom_slug(): void
    {
        $category = Category::create([
            'name' => 'Custom Category',
            'slug' => 'custom-slug',
            'description' => 'Custom Description',
        ]);

        $this->assertEquals('Custom Category', $category->name);
        $this->assertEquals('custom-slug', $category->slug);
        $this->assertEquals('Custom Description', $category->description);
    }

    #[Test]
    public function it_auto_generates_slug_on_update_when_name_changes_and_slug_empty(): void
    {
        $category = Category::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        // Обновляем имя, но оставляем slug - slug не должен измениться
        $category->update(['name' => 'New Name']);
        $this->assertEquals('original-slug', $category->slug);

        // Обновляем имя и очищаем slug - должен сгенерироваться новый
        $category->update(['name' => 'Another Name', 'slug' => '']);
        $this->assertEquals('another-name', $category->slug);
    }

    #[Test]
    public function posts_relationship_method_returns_has_many(): void
    {
        $category = Category::factory()->create();

        // ЯВНО вызываем метод posts() для покрытия
        $postsRelation = $category->posts();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $postsRelation);
        $this->assertEquals('App\Models\Post', $postsRelation->getRelated()::class);
    }

    #[Test]
    public function it_has_one_to_many_relationship_with_posts(): void
    {
        $category = Category::factory()->create();
        $post1 = Post::factory()->create(['category_id' => $category->id]);
        $post2 = Post::factory()->create(['category_id' => $category->id]);

        // Проверяем связь
        $this->assertCount(2, $category->posts);
        $this->assertTrue($category->posts->contains($post1));
        $this->assertTrue($category->posts->contains($post2));

        // Проверяем обратную связь
        $this->assertTrue($post1->category->is($category));
        $this->assertTrue($post2->category->is($category));
    }

    #[Test]
    public function booted_method_handles_creating_event(): void
    {
        // Тестируем creating event - это должно покрыть метод booted()
        $category = new Category(['name' => 'Creating Test']);
        $category->save();

        $this->assertEquals('creating-test', $category->slug);
    }

    #[Test]
    public function booted_method_handles_updating_event(): void
    {
        $category = Category::create([
            'name' => 'Original',
            'slug' => 'original',
        ]);

        // Изменяем имя, но slug не пустой - не должен измениться
        $category->update(['name' => 'Updated']);
        $this->assertEquals('original', $category->fresh()->slug);

        // Изменяем имя и очищаем slug - должен сгенерироваться
        $category->update(['name' => 'Final Update', 'slug' => '']);
        $this->assertEquals('final-update', $category->fresh()->slug);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $category = new Category();

        $this->assertEquals(['name', 'description', 'slug'], $category->getFillable());
    }

    #[Test]
    public function booted_method_is_called_during_model_lifecycle(): void
    {
        // Дополнительный тест для явного вызова booted() логики
        $category1 = Category::create(['name' => 'Test 1']);
        $this->assertEquals('test-1', $category1->slug);

        $category2 = Category::create(['name' => 'Test 2', 'slug' => 'custom']);
        $this->assertEquals('custom', $category2->slug);

        // Тестируем updating event
        $category1->name = 'Updated Test';
        $category1->slug = null; // Очищаем slug
        $category1->save();
        $this->assertEquals('updated-test', $category1->slug);
    }

    #[Test]
    public function posts_relationship_can_be_used_for_queries(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        Post::factory()->count(3)->create(['category_id' => $category->id]);
        Post::factory()->count(2)->create(); // Посты в других категориях

        // Используем отношение posts() в запросе
        $categoryPosts = $category->posts()->get();
        $this->assertCount(3, $categoryPosts);

        // Проверяем, что отношение работает
        $this->assertEquals(3, $category->posts()->count());
        $this->assertEquals(3, $category->posts->count());
    }
}
