<?php
// tests/Feature/PostShowTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Post;
use App\Models\User;

class PostShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_page_displays_image_and_tags(): void
    {
        Storage::fake('public');
        $u = User::factory()->create();

        $path = UploadedFile::fake()->image('img.jpg', 1200, 675)->store('posts', 'public');

        $post = Post::create([
            'title' => 'Просмотр',
            'content' => '...',
            'user_id' => $u->id,
            'image' => $path,
            'date' => now()->toDateString(),
        ]);

        $res = $this->get(route('posts.show', $post));
        $res->assertOk()->assertSee('/storage/'.$path)->assertSee('Просмотр');
    }
}