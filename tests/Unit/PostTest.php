<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_post()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now(),
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('This is a test post content.', $post->content);
        $this->assertEquals($user->id, $post->user_id);
    }

    public function test_post_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_post_belongs_to_category()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->id, $post->category->id);
    }

    public function test_post_has_many_comments()
    {
        $post = Post::factory()->create();
        $comment = $post->comments()->create([
            'author' => 'Test Commenter',
            'content' => 'Test comment content',
        ]);

        $this->assertCount(1, $post->comments);
        $this->assertEquals('Test Commenter', $post->comments->first()->author);
    }

    public function test_post_can_have_tags()
    {
        $post = Post::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'PHP']);
        $tag2 = Tag::factory()->create(['name' => 'Laravel']);

        $post->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertCount(2, $post->tags);
        $this->assertTrue($post->tags->contains($tag1));
        $this->assertTrue($post->tags->contains($tag2));
    }

    public function test_post_date_is_cast_to_date()
    {
        $post = Post::factory()->create(['date' => '2023-01-15']);

        $this->assertInstanceOf(\Carbon\Carbon::class, $post->date);
        $this->assertEquals('2023-01-15', $post->date->toDateString());
    }
}