<?php
// tests/Feature/PostIndexTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Category;
use App\Models\User;

class PostIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_post_with_image_and_tags(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'PHP']);
        $tag1 = Tag::create(['name' => 'API']);
        $tag2 = Tag::create(['name' => 'CSS']);
        $user = User::factory()->create();

        $path = UploadedFile::fake()->image('img.jpg', 1200, 675)->store('posts', 'public');

        $post = Post::create([
            'title' => 'Тестовый пост',
            'content' => 'Контент',
            'category_id' => $category->id,
            'user_id' => $user->id,
            'image' => $path,
            'date' => now()->toDateString(),
            'published_at' => now(),
        ]);
        $post->tags()->sync([$tag1->id, $tag2->id]);

        $res = $this->get(route('posts.index'));
        $res->assertOk()
            ->assertSee('Тестовый пост')
            ->assertSee('#'.$tag1->name)
            ->assertSee('#'.$tag2->name)
            ->assertSee('/storage/'.$path); // картинка выводится
    }

    public function test_filter_by_tag_and_category_and_search(): void
    {
        $catPhp = Category::create(['name' => 'PHP']);
        $catJs  = Category::create(['name' => 'JS']);
        $tagApi = Tag::create(['name' => 'API']);

        $u = User::factory()->create();

        $p1 = Post::create([
            'title' => 'Laravel Guide',
            'content' => '...',
            'category_id' => $catPhp->id,
            'user_id' => $u->id,
            'date' => now()->toDateString(),
            'published_at' => now(),
        ]);
        $p1->tags()->sync([$tagApi->id]);

        $p2 = Post::create([
            'title' => 'Vue Handbook',
            'content' => '...',
            'category_id' => $catJs->id,
            'user_id' => $u->id,
            'date' => now()->toDateString(),
        ]);

        // поиск по title
        $this->get(route('posts.index', ['search' => 'Laravel']))
            ->assertOk()->assertSee('Laravel Guide')->assertDontSee('Vue Handbook');

        // категория
        $this->get(route('posts.index', ['category' => $catPhp->id]))
            ->assertOk()->assertSee('Laravel Guide')->assertDontSee('Vue Handbook');

        // тег
        $this->get(route('posts.index', ['tag' => $tagApi->id]))
            ->assertOk()->assertSee('Laravel Guide')->assertDontSee('Vue Handbook');
    }
}