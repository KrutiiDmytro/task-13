<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostControllerTest extends AdminTestCase
{
    public function test_admin_can_list_posts()
    {
        Post::factory()->count(3)->create();

        $this->actingAsAdmin()
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertViewIs('admin.posts.index')
            ->assertViewHas(['posts', 'categories', 'tags']);
    }

    public function test_admin_can_view_create_post_form()
    {
        $this->actingAsAdmin()
            ->get(route('admin.posts.create'))
            ->assertOk()
            ->assertViewIs('admin.posts.create')
            ->assertViewHas(['categories', 'tags']);
    }

    public function test_admin_can_create_post()
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $postData = [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'category_id' => $category->id,
            'tags' => [$tag->name]
        ];

        $this->actingAsAdmin()
            ->post(route('admin.posts.store'), $postData)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'category_id' => $category->id,
            'user_id' => $this->admin->id
        ]);
    }

    public function test_admin_can_create_post_with_image()
    {
        Storage::fake('public');
        
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('test.jpg');

        $postData = [
            'title' => 'Test Post with Image',
            'content' => 'Test Content',
            'category_id' => $category->id,
            'image' => $image
        ];

        $this->actingAsAdmin()
            ->post(route('admin.posts.store'), $postData)
            ->assertRedirect(route('admin.posts.index'));

        $post = Post::where('title', 'Test Post with Image')->first();
        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_admin_can_show_post()
    {
        $post = Post::factory()->create();

        $this->actingAsAdmin()
            ->get(route('admin.posts.show', $post))
            ->assertOk()
            ->assertViewIs('admin.posts.show')
            ->assertViewHas('post');
    }

    public function test_admin_can_edit_post()
    {
        $post = Post::factory()->create();

        $this->actingAsAdmin()
            ->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertViewIs('admin.posts.edit')
            ->assertViewHas(['post', 'categories', 'tags']);
    }

    public function test_admin_can_update_post()
    {
        $post = Post::factory()->create();
        $category = Category::factory()->create();

        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated Content',
            'category_id' => $category->id
        ];

        $this->actingAsAdmin()
            ->put(route('admin.posts.update', $post), $updateData)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post Title',
            'content' => 'Updated Content',
            'category_id' => $category->id
        ]);
    }

    public function test_admin_can_delete_post()
    {
        $post = Post::factory()->create();

        $this->actingAsAdmin()
            ->delete(route('admin.posts.destroy', $post))
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_post_creation_validation()
    {
        $this->actingAsAdmin()
            ->post(route('admin.posts.store'), [])
            ->assertSessionHasErrors(['title', 'content']);
    }

    public function test_regular_user_cannot_access_posts()
    {
        $this->actingAsRegularUser()
            ->get(route('admin.posts.index'))
            ->assertStatus(403);
    }
    
        public function test_admin_can_update_post_with_new_image()
    {
        Storage::fake('public');
        
        $post = Post::factory()->create(['image' => 'posts/old-image.jpg']);
        
        // Создаем фейковое старое изображение
        Storage::disk('public')->put('posts/old-image.jpg', 'fake old image content');
        
        $newImage = UploadedFile::fake()->image('new-image.jpg');
        
        $updateData = [
            'title' => 'Updated Post with Image',
            'content' => 'Updated Content',
            'image' => $newImage
        ];

        $this->actingAsAdmin()
            ->put(route('admin.posts.update', $post), $updateData)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $post->refresh();
        
        // Проверяем, что старое изображение удалено
        Storage::disk('public')->assertMissing('posts/old-image.jpg');
        
        // Проверяем, что новое изображение сохранено
        $this->assertNotNull($post->image);
        $this->assertStringContainsString('posts/', $post->image);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_admin_can_update_post_with_image_when_no_previous_image()
    {
        Storage::fake('public');
        
        $post = Post::factory()->create(['image' => null]);
        
        $newImage = UploadedFile::fake()->image('first-image.jpg');
        
        $updateData = [
            'title' => 'Updated Post with First Image',
            'content' => 'Updated Content',
            'image' => $newImage
        ];

        $this->actingAsAdmin()
            ->put(route('admin.posts.update', $post), $updateData)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $post->refresh();
        
        // Проверяем, что изображение сохранено
        $this->assertNotNull($post->image);
        $this->assertStringContainsString('posts/', $post->image);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_admin_can_update_post_without_changing_image()
    {
        Storage::fake('public');
        
        $post = Post::factory()->create(['image' => 'posts/existing-image.jpg']);
        
        // Создаем фейковое существующее изображение
        Storage::disk('public')->put('posts/existing-image.jpg', 'fake existing image content');
        
        $updateData = [
            'title' => 'Updated Post without Image Change',
            'content' => 'Updated Content'
            // Не передаем image
        ];

        $this->actingAsAdmin()
            ->put(route('admin.posts.update', $post), $updateData)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $post->refresh();
        
        // Проверяем, что старое изображение осталось
        $this->assertEquals('posts/existing-image.jpg', $post->image);
        Storage::disk('public')->assertExists('posts/existing-image.jpg');
    }

    public function test_admin_can_update_post_image_replacement()
    {
        Storage::fake('public');
        
        $post = Post::factory()->create(['image' => 'posts/old-image.jpg']);
        
        // Создаем фейковое старое изображение
        Storage::disk('public')->put('posts/old-image.jpg', 'fake old image content');
        
        $newImage = UploadedFile::fake()->image('replacement.jpg');
        
        $updateData = [
            'title' => $post->title, // Оставляем прежний заголовок
            'content' => $post->content, // Оставляем прежний контент
            'image' => $newImage
        ];

        $this->actingAsAdmin()
            ->put(route('admin.posts.update', $post), $updateData)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $post->refresh();
        
        // Проверяем, что старое изображение удалено (покрывает строки 101-103)
        Storage::disk('public')->assertMissing('posts/old-image.jpg');
        
        // Проверяем, что новое изображение сохранено (покрывает строки 104-106)
        $this->assertNotNull($post->image);
        $this->assertNotEquals('posts/old-image.jpg', $post->image);
        Storage::disk('public')->assertExists($post->image);
    }
}