<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_tag_with_auto_generated_slug(): void
    {
        $tag = Tag::create(['name' => 'Test Tag']);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('Test Tag', $tag->name);
        $this->assertEquals('test-tag', $tag->slug);
        $this->assertDatabaseHas('tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);
    }

    #[Test]
    public function it_can_create_tag_with_custom_slug(): void
    {
        $tag = Tag::create([
            'name' => 'Custom Tag',
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('Custom Tag', $tag->name);
        $this->assertEquals('custom-slug', $tag->slug);
        $this->assertDatabaseHas('tags', [
            'name' => 'Custom Tag',
            'slug' => 'custom-slug',
        ]);
    }

    #[Test]
    public function it_auto_generates_slug_only_when_empty(): void
    {
        // Создаем тег с пустым slug - должен сгенерироваться
        $tag1 = Tag::create(['name' => 'Auto Slug', 'slug' => '']);
        $this->assertEquals('auto-slug', $tag1->slug);

        // Создаем тег с заданным slug - не должен изменяться
        $tag2 = Tag::create(['name' => 'Manual Slug', 'slug' => 'manual']);
        $this->assertEquals('manual', $tag2->slug);
    }

    #[Test]
    public function it_handles_special_characters_in_slug_generation(): void
    {
        $tag = Tag::create(['name' => 'PHP & Laravel Framework!']);

        $this->assertEquals('php-laravel-framework', $tag->slug);
    }

    #[Test]
    public function posts_relationship_method_returns_belongs_to_many(): void
    {
        $tag = Tag::factory()->create();

        // ЯВНО вызываем метод posts() для покрытия
        $postsRelation = $tag->posts();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $postsRelation);
        $this->assertEquals('App\Models\Post', $postsRelation->getRelated()::class);
    }

    #[Test]
    public function it_has_many_to_many_relationship_with_posts(): void
    {
        $tag = Tag::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        // Привязываем посты к тегу
        $tag->posts()->attach([$post1->id, $post2->id]);

        // Проверяем связь
        $this->assertCount(2, $tag->posts);
        $this->assertTrue($tag->posts->contains($post1));
        $this->assertTrue($tag->posts->contains($post2));

        // Проверяем обратную связь
        $this->assertTrue($post1->tags->contains($tag));
        $this->assertTrue($post2->tags->contains($tag));
    }

    #[Test]
    public function posts_relationship_uses_correct_pivot_table(): void
    {
        $tag = Tag::factory()->create();
        $post = Post::factory()->create();

        $tag->posts()->attach($post->id);

        // Проверяем, что запись создалась в правильной таблице
        $this->assertDatabaseHas('post_tag', [
            'tag_id' => $tag->id,
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $tag = new Tag();

        $this->assertEquals(['name', 'slug'], $tag->getFillable());
    }

    #[Test]
    public function booted_method_is_called_during_creation(): void
    {
        // Этот тест проверяет, что booted() метод вызывается
        // Создаем тег без slug - если booted() работает, slug сгенерируется
        $tag = new Tag(['name' => 'Booted Test']);
        $tag->save();

        $this->assertEquals('booted-test', $tag->slug);
    }
}
