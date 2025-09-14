<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестируем связь "один ко многим" между User и Post.
     */
    public function test_user_has_many_posts(): void
    {
        // Создаем пользователя
        $user = User::factory()->create();

        // Создаем два поста для этого пользователя
        Post::factory()->create(['user_id' => $user->id]);
        Post::factory()->create(['user_id' => $user->id]);

        // Проверяем связь posts()
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->posts);
        $this->assertCount(2, $user->posts);

        // Проверяем, что посты действительно принадлежат пользователю
        foreach ($user->posts as $post) {
            $this->assertEquals($user->id, $post->user_id);
        }
    }

    /**
     * Тестируем связь "один ко многим" между User и Comment через поле author.
     */
    public function test_user_has_many_comments(): void
    {
        // Создаем пользователя
        $user = User::factory()->create(['name' => 'Test Author']);

        // Создаем пост
        $post = Post::factory()->create();

        // Создаем комментарии с именем пользователя в поле author
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

        // Проверяем связь comments()
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comments);
        $this->assertCount(2, $user->comments);

        // Проверяем, что комментарии действительно принадлежат пользователю
        foreach ($user->comments as $comment) {
            $this->assertEquals($user->name, $comment->author);
        }
    }

    /**
     * Тестируем метод isAdmin() для обычного пользователя.
     */
    public function test_is_admin_returns_false_for_regular_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertFalse($user->isAdmin());
    }

    /**
     * Тестируем метод isAdmin() для администратора.
     */
    public function test_is_admin_returns_true_for_admin_user(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($user->isAdmin());
    }

    /**
     * Тестируем метод casts() - проверяем, что поля кастятся правильно.
     */
    public function test_casts_method_returns_correct_casting_rules(): void
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
        $this->assertEquals('boolean', $casts['is_admin']);
    }

    /**
     * Тестируем, что пользователь без комментариев возвращает пустую коллекцию.
     */
    public function test_user_without_comments_returns_empty_collection(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comments);
        $this->assertCount(0, $user->comments);
    }

    /**
     * Тестируем, что пользователь без постов возвращает пустую коллекцию.
     */
    public function test_user_without_posts_returns_empty_collection(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->posts);
        $this->assertCount(0, $user->posts);
    }

    /**
     * Тестируем связь comments() с разными пользователями.
     */
    public function test_comments_relationship_distinguishes_between_users(): void
    {
        // Создаем двух пользователей
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        // Создаем пост
        $post = Post::factory()->create();

        // Создаем комментарии от разных пользователей
        Comment::factory()->create([
            'post_id' => $post->id,
            'author' => $user1->name,
            'content' => 'Comment by User One'
        ]);
        Comment::factory()->create([
            'post_id' => $post->id,
            'author' => $user2->name,
            'content' => 'Comment by User Two'
        ]);

        // Проверяем, что каждый пользователь видит только свои комментарии
        $this->assertCount(1, $user1->comments);
        $this->assertCount(1, $user2->comments);

        $this->assertEquals('User One', $user1->comments->first()->author);
        $this->assertEquals('User Two', $user2->comments->first()->author);
    }
}