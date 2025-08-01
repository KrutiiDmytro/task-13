<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;

   
    public function tag_has_many_posts(): void
    {
        $tag   = Tag::factory()->create();
        $posts = Post::factory()->count(3)->create();

        // привязываем посты к тегу
        $tag->posts()->attach($posts->pluck('id'));

        $tag->load('posts');           // подгружаем отношение

        $this->assertCount(3, $tag->posts);
        $this->assertInstanceOf(Post::class, $tag->posts->first());
    }
}