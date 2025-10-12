<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_post_with_image_and_tags(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $cat = Category::create(['name' => 'PHP']);
        $file = UploadedFile::fake()->image('cover.jpg', 1200, 675);

        $existing = Tag::create(['name' => 'CSS']);

        $res = $this->post(route('posts.store'), [
            'title' => 'Новый пост',
            'content' => 'Текст',
            'category_id' => $cat->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'tags' => ['API', $existing->id],
            'image' => $file,
        ]);

        $res->assertRedirect();
    }

    public function test_guest_cannot_create_post(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('posts.store'), [
            'title' => 'От гостя',
            'content' => 'Контент',
            'category_id' => $category->id,
            'author_name' => 'Гость',
            'author_email' => 'guest@example.com',
        ]);

        // Гости должны быть перенаправлены на страницу логина
        $response->assertRedirect('/login');

        // Пост не должен быть создан
        $this->assertDatabaseMissing('posts', [
            'title' => 'От гостя',
            'author_name' => 'Гость',
            'author_email' => 'guest@example.com',
        ]);
    }
}
