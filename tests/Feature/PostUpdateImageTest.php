<?php
// tests/Feature/PostUpdateImageTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;

class PostUpdateImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_replace_image_and_old_deleted(): void
    {
        Storage::fake('public');
        $u = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($u);

        $old = UploadedFile::fake()->image('old.jpg', 1200, 675)->store('posts', 'public');
        $post = Post::create([
            'title' => 'С картинкой',
            'content' => '...',
            'user_id' => $u->id,
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'image' => $old,
            'date' => now()->toDateString(),
        ]);

        $new = UploadedFile::fake()->image('new.jpg', 1200, 675);

        $res = $this->put(route('posts.update', $post), [
            'title' => 'С картинкой',
            'content' => 'Обновлено',
            'category_id' => $category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'image' => $new,
        ]);

        $res->assertRedirect();
        $post->refresh();
        $this->assertNotEquals($old, $post->image);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($post->image);
    }
}