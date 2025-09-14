<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестуємо зв'язок "один до багатьох" між Постом та Коментарями.
     */
    #[Test]
    public function a_post_has_many_comments(): void
    {
        // 1. Створюємо пост
        $post = Post::factory()->create();

        // 2. Створюємо два коментарі, пов'язані з цим постом
        Comment::factory()->create(['post_id' => $post->id]);
        Comment::factory()->create(['post_id' => $post->id]);

        // 3. Перевіряємо, що у поста дійсно є зв'язок 'comments' і це колекція
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $post->comments);

        // 4. Переконуємося, що в колекції рівно 2 коментарі
        $this->assertCount(2, $post->comments);
    }

    /**
     * Тестуємо зворотний зв'язок "належить до" між Коментарем та Постом.
     */
    #[Test]
    public function a_comment_belongs_to_a_post(): void
    {
        // 1. Створюємо пост
        $post = Post::factory()->create();

        // 2. Створюємо коментар, пов'язаний з цим постом
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        // 3. Перевіряємо, що у коментаря є зв'язок 'post' і він є екземпляром класу Post
        $this->assertInstanceOf(Post::class, $comment->post);

        // 4. Переконуємося, що ID поста в коментарі збігається з ID створеного поста
        $this->assertEquals($post->id, $comment->post->id);
    }

    /**
     * Тестуємо зв'язок "багато до багатьох" між Постом та Тегами.
     */
    #[Test]
    public function a_post_can_have_many_tags(): void
    {
        // 1. Створюємо пост
        $post = Post::factory()->create();

        // 2. Створюємо 3 теги
        $tags = Tag::factory(3)->create();

        // 3. Прикріплюємо теги до поста
        $post->tags()->attach($tags->pluck('id'));

        // 4. Перевіряємо, що у поста 3 теги і це колекція
        $this->assertCount(3, $post->tags);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $post->tags);
    }

    /**
     * Тестуємо зв'язок "належить до" між Постом та Категорією.
     */
    #[Test]
    public function a_post_belongs_to_a_category(): void
    {
        // 1. Створюємо категорію
        $category = Category::factory()->create();

        // 2. Створюємо пост, що належить до цієї категорії
        $post = Post::factory()->create(['category_id' => $category->id]);

        // 3. Перевіряємо, що зв'язок 'category' існує і є екземпляром класу Category
        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->name, $post->category->name);
    }

    // === Тесты для модели User ===

    /**
     * Тестируем связь "один ко многим" между User и Post.
     */
    #[Test]
    public function a_user_has_many_posts(): void
    {
        // 1. Создаем пользователя
        $user = User::factory()->create();

        // 2. Создаем два поста, связанных с этим пользователем
        Post::factory()->create(['user_id' => $user->id]);
        Post::factory()->create(['user_id' => $user->id]);

        // 3. Проверяем, что у пользователя есть связь 'posts' и это коллекция
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->posts);

        // 4. Убеждаемся, что в коллекции ровно 2 поста
        $this->assertCount(2, $user->posts);
    }

    /**
     * Тестируем связь "один ко многим" между User и Comment.
     */
    #[Test]
    public function a_user_has_many_comments(): void
    {
        // 1. Создаем пользователя
        $user = User::factory()->create(['name' => 'Test Author']);

        // 2. Создаем пост
        $post = Post::factory()->create();

        // 3. Создаем два комментария от этого пользователя
        Comment::factory()->create([
            'post_id' => $post->id,
            'author' => $user->name,
            'content' => 'First comment'
        ]);
        Comment::factory()->create([
            'post_id' => $post->id,
            'author' => $user->name,
            'content' => 'Second comment'
        ]);

        // 4. Проверяем связь comments()
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comments);
        $this->assertCount(2, $user->comments);

        // 5. Проверяем, что комментарии действительно принадлежат пользователю
        foreach ($user->comments as $comment) {
            $this->assertEquals($user->name, $comment->author);
        }
    }
}