<?php

namespace Tests\Unit\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'admin' => true,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue($user->admin);
    }

    #[Test]
    public function posts_relationship_method_returns_has_many(): void
    {
        $user = User::factory()->create();

        // ЯВНО вызываем метод posts() для покрытия
        $postsRelation = $user->posts();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $postsRelation);
        $this->assertEquals('App\Models\Post', $postsRelation->getRelated()::class);
    }

    #[Test]
    public function it_has_one_to_many_relationship_with_posts(): void
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create(['user_id' => $user->id]);
        $post2 = Post::factory()->create(['user_id' => $user->id]);

        // Проверяем связь
        $this->assertCount(2, $user->posts);
        $this->assertTrue($user->posts->contains($post1));
        $this->assertTrue($user->posts->contains($post2));

        // Проверяем обратную связь
        $this->assertTrue($post1->user->is($user));
        $this->assertTrue($post2->user->is($user));
    }

    #[Test]
    public function comments_relationship_method_returns_has_many(): void
    {
        $user = User::factory()->create();

        // ЯВНО вызываем метод comments() для покрытия
        $commentsRelation = $user->comments();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $commentsRelation);
        $this->assertEquals('App\Models\Comment', $commentsRelation->getRelated()::class);
    }

    #[Test]
    public function it_has_one_to_many_relationship_with_comments(): void
    {
        $user = User::factory()->create();
        $comment1 = Comment::factory()->create(['user_id' => $user->id]);
        $comment2 = Comment::factory()->create(['user_id' => $user->id]);

        // Проверяем связь
        $this->assertCount(2, $user->comments);
        $this->assertTrue($user->comments->contains($comment1));
        $this->assertTrue($user->comments->contains($comment2));
    }

    #[Test]
    public function casts_method_returns_correct_array(): void
    {
        $user = new User;

        // ЯВНО вызываем метод casts() для покрытия
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
        $this->assertEquals('boolean', $casts['admin']);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $user = new User;

        $this->assertEquals(['name', 'email', 'password', 'admin'], $user->getFillable());
    }

    #[Test]
    public function it_has_correct_hidden_attributes(): void
    {
        $user = new User;

        $this->assertEquals(['password', 'remember_token'], $user->getHidden());
    }

    #[Test]
    public function password_is_hashed_automatically(): void
    {
        $user = User::factory()->create(['password' => 'plain-password']);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(\Hash::check('plain-password', $user->password));
    }

    #[Test]
    public function admin_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['admin' => 1]);

        $this->assertIsBool($user->admin);
        $this->assertTrue($user->admin);

        $user2 = User::factory()->create(['admin' => 0]);
        $this->assertIsBool($user2->admin);
        $this->assertFalse($user2->admin);
    }
}
