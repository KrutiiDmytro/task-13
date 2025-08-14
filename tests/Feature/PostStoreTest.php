<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Category;

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
        'title'       => 'Новый пост',
        'content'     => 'Текст',
        'category_id' => $cat->id,
        'tags'        => ['API', $existing->id], 
        'image'       => $file,
    ]);

    $res->assertRedirect();
    
    }

    public function test_guest_can_create_post_with_guest_author(): void
    {
        $res = $this->post(route('posts.store'), [
            'title' => 'От гостя',
            'content' => 'Контент',
            'author_name' => 'Гость',
            'author_email' => 'guest@example.com',
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'От гостя',
            'author_name' => 'Гость',
            'author_email' => 'guest@example.com',
        ]);
    }
}