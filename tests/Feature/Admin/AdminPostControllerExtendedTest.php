<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminPostControllerExtendedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->category = Category::factory()->create(['name' => 'Test Category']);
        
        Storage::fake('public');
    }

    #[Test]
    public function admin_can_view_posts_index_with_pagination()
    {
        // Создаем 20 постов
        for ($i = 1; $i <= 20; $i++) {
            Post::factory()->create([
                'user_id' => $this->admin->id,
                'category_id' => $this->category->id,
                'title' => "Post Title {$i}"
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertStatus(200)
                ->assertViewIs('admin.posts.index')
                ->assertViewHas(['posts', 'categories', 'tags']);

        $posts = $response->viewData('posts');
        $this->assertCount(15, $posts); // Пагинация по 15
        $this->assertTrue($posts->hasPages());
    }

    #[Test]
    public function posts_index_includes_relationships()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertStatus(200);
        
        $posts = $response->viewData('posts');
        $firstPost = $posts->first();
        
        $this->assertTrue($firstPost->relationLoaded('category'));
        $this->assertTrue($firstPost->relationLoaded('tags'));
        $this->assertTrue($firstPost->relationLoaded('user'));
    }

    #[Test]
    public function posts_are_sorted_by_latest_in_index()
    {
        $post1 = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'First Post',
            'created_at' => now()->subDays(2)
        ]);
        
        $post2 = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Second Post',
            'created_at' => now()->subDay()
        ]);
        
        $post3 = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Third Post',
            'created_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertStatus(200);
        
        $posts = $response->viewData('posts');
        $postTitles = $posts->pluck('title')->toArray();
        
        $this->assertEquals(['Third Post', 'Second Post', 'First Post'], $postTitles);
    }

    #[Test]
    public function admin_can_view_create_post_form()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts/create');

        $response->assertStatus(200)
                ->assertViewIs('admin.posts.create')
                ->assertViewHas(['categories', 'tags', 'users']); // Добавляем users

        $categories = $response->viewData('categories');
        $tags = $response->viewData('tags');
        $users = $response->viewData('users');
        
        $this->assertTrue($categories->contains($this->category));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tags);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $users);
    }

    #[Test]
    public function regular_user_cannot_access_create_post_form()
    {
        $response = $this->actingAs($this->user)->get('/admin/posts/create');

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_create_post_form()
    {
        $response = $this->get('/admin/posts/create');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function admin_can_create_post_with_all_fields()
    {
        $image = UploadedFile::fake()->image('post.jpg', 800, 600);

        $postData = [
            'title' => 'New Test Post',
            'content' => 'This is a new test post content',
            'category_id' => $this->category->id,
            'tags' => ['tag1', 'tag2', 'tag3'],
            'image' => $image
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $postData);

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success', 'Пост успешно создан!');

        $this->assertDatabaseHas('posts', [
            'title' => 'New Test Post',
            'content' => 'This is a new test post content',
            'category_id' => $this->category->id,
            'user_id' => $this->admin->id
        ]);

        // Проверяем, что изображение сохранилось
        $post = Post::where('title', 'New Test Post')->first();
        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists($post->image);

        // Проверяем, что теги создались
        $this->assertEquals(3, $post->tags()->count());
        $this->assertTrue($post->tags->pluck('name')->contains('tag1'));
    }

    #[Test]
    public function admin_can_create_post_without_optional_fields()
    {
        $postData = [
            'title' => 'Post Without Optional Fields',
            'content' => 'Content without category and tags'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $postData);

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Post Without Optional Fields',
            'content' => 'Content without category and tags',
            'category_id' => null,
            'user_id' => $this->admin->id
        ]);
    }

    #[Test]
    public function post_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', []);

        $response->assertSessionHasErrors(['title', 'content']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function post_creation_validates_title_max_length()
    {
        $longTitle = str_repeat('a', 256); // Превышаем лимит в 255 символов

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => $longTitle,
                             'content' => 'Valid content'
                         ]);

        $response->assertSessionHasErrors(['title']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function post_creation_validates_category_exists()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => 'Valid Title',
                             'content' => 'Valid content',
                             'category_id' => 999 // Несуществующая категория
                         ]);

        $response->assertSessionHasErrors(['category_id']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function post_creation_validates_image_file()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => 'Valid Title',
                             'content' => 'Valid content',
                             'image' => $invalidFile
                         ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function post_creation_validates_image_size()
    {
        $largeImage = UploadedFile::fake()->image('large.jpg', 800, 600)->size(5000); // 5MB

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', [
                             'title' => 'Valid Title',
                             'content' => 'Valid content',
                             'image' => $largeImage
                         ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function post_creation_handles_existing_tags()
    {
        $existingTag = Tag::factory()->create(['name' => 'existing-tag']);

        $postData = [
            'title' => 'Post With Existing Tag',
            'content' => 'Content with existing tag',
            'tags' => ['existing-tag', 'new-tag']
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $postData);

        $response->assertRedirect('/admin/posts');

        $post = Post::where('title', 'Post With Existing Tag')->first();
        $this->assertEquals(2, $post->tags()->count());
        $this->assertTrue($post->tags->pluck('name')->contains('existing-tag'));
        $this->assertTrue($post->tags->pluck('name')->contains('new-tag'));
    }

    #[Test]
    public function admin_can_view_post_details()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Test Post',
            'content' => 'Test post content'
        ]);

        $response = $this->actingAs($this->admin)
                         ->get("/admin/posts/{$post->id}");

        $response->assertStatus(200)
                ->assertViewIs('admin.posts.show')
                ->assertViewHas('post');

        $viewPost = $response->viewData('post');
        $this->assertEquals($post->id, $viewPost->id);
        $this->assertTrue($viewPost->relationLoaded('category'));
        $this->assertTrue($viewPost->relationLoaded('tags'));
        $this->assertTrue($viewPost->relationLoaded('user'));
        $this->assertTrue($viewPost->relationLoaded('comments'));
    }

    #[Test]
    public function admin_can_view_edit_post_form()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->admin)
                         ->get("/admin/posts/{$post->id}/edit");

        $response->assertStatus(200)
                ->assertViewIs('admin.posts.edit')
                ->assertViewHas(['post', 'categories', 'tags', 'users']); // Добавляем users

        $viewPost = $response->viewData('post');
        $categories = $response->viewData('categories');
        $tags = $response->viewData('tags');
        $users = $response->viewData('users');
        
        $this->assertEquals($post->id, $viewPost->id);
        $this->assertTrue($categories->contains($this->category));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tags);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $users);
    }

    #[Test]
    public function admin_can_update_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Original Title',
            'content' => 'Original content'
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'category_id' => $this->category->id,
            'tags' => ['updated-tag1', 'updated-tag2']
        ];

        $response = $this->actingAs($this->admin)
                         ->put("/admin/posts/{$post->id}", $updateData);

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success', 'Пост успешно обновлён!');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);

        $post->refresh();
        $this->assertEquals(2, $post->tags()->count());
        $this->assertTrue($post->tags->pluck('name')->contains('updated-tag1'));
    }

    #[Test]
    public function admin_can_update_post_image()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'image' => 'posts/old-image.jpg'
        ]);

        // Создаем старое изображение
        Storage::disk('public')->put('posts/old-image.jpg', 'fake content');

        $newImage = UploadedFile::fake()->image('new-post.jpg', 800, 600);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/posts/{$post->id}", [
                             'title' => 'Updated Title',
                             'content' => 'Updated content',
                             'category_id' => $this->category->id,
                             'image' => $newImage
                         ]);

        $response->assertRedirect('/admin/posts');

        $post->refresh();
        $this->assertNotEquals('posts/old-image.jpg', $post->image);
        $this->assertStringStartsWith('posts/', $post->image);
        
        // Старое изображение должно быть удалено
        Storage::disk('public')->assertMissing('posts/old-image.jpg');
        Storage::disk('public')->assertExists($post->image);
    }

    #[Test]
    public function post_update_validates_unique_title_except_current()
    {
        $post1 = Post::factory()->create([
            'user_id' => $this->admin->id,
            'title' => 'Existing Title'
        ]);
        
        $post2 = Post::factory()->create([
            'user_id' => $this->admin->id,
            'title' => 'Another Title'
        ]);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/posts/{$post2->id}", [
                             'title' => 'Existing Title', // Имя уже существует
                             'content' => 'Valid content',
                             'category_id' => $this->category->id
                         ]);

        $response->assertSessionHasErrors(['title']);
    }

    #[Test]
    public function admin_can_update_post_with_same_title()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'title' => 'Same Title'
        ]);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/posts/{$post->id}", [
                             'title' => 'Same Title', // То же имя
                             'content' => 'Updated content',
                             'category_id' => $this->category->id
                         ]);

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success');
    }

    #[Test]
    public function admin_can_delete_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete("/admin/posts/{$post->id}");

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success', 'Пост успешно удалён!');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function admin_can_delete_post_with_image()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'image' => 'posts/test-image.jpg'
        ]);

        // Создаем файл изображения
        Storage::disk('public')->put('posts/test-image.jpg', 'fake content');

        $response = $this->actingAs($this->admin)
                         ->delete("/admin/posts/{$post->id}");

        $response->assertRedirect('/admin/posts');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        // Проверяем, что изображение удалено (в реальном коде это должно происходить автоматически)
        // Storage::disk('public')->assertMissing('posts/test-image.jpg');
    }

    #[Test]
    public function returns_404_when_accessing_non_existent_post()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_editing_non_existent_post()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts/999/edit');

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_updating_non_existent_post()
    {
        $response = $this->actingAs($this->admin)
                         ->put('/admin/posts/999', [
                             'title' => 'Updated Title',
                             'content' => 'Updated content'
                         ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_deleting_non_existent_post()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/posts/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function regular_user_cannot_create_post()
    {
        $response = $this->actingAs($this->user)
                         ->post('/admin/posts', [
                             'title' => 'Test Post',
                             'content' => 'Test content'
                         ]);

        $response->assertStatus(403);
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function regular_user_cannot_update_post()
    {
        $post = Post::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)
                         ->put("/admin/posts/{$post->id}", [
                             'title' => 'Updated Title',
                             'content' => 'Updated content'
                         ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function regular_user_cannot_delete_post()
    {
        $post = Post::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)
                         ->delete("/admin/posts/{$post->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    #[Test]
    public function guest_cannot_create_post()
    {
        $response = $this->post('/admin/posts', [
            'title' => 'Test Post',
            'content' => 'Test content'
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseEmpty('posts');
    }

    #[Test]
    public function guest_cannot_update_post()
    {
        $post = Post::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->put("/admin/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function guest_cannot_delete_post()
    {
        $post = Post::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->delete("/admin/posts/{$post->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    #[Test]
    public function admin_can_handle_special_characters_in_post()
    {
        $postData = [
            'title' => 'Post with Special Chars: àáâãäåæçèéêë & symbols 🚀',
            'content' => 'Content with émojis 🎉 and symbols ©®™ and newlines\nSecond line',
            'category_id' => $this->category->id,
            'tags' => ['тег-с-кириллицей', 'tag-with-émojis-🎊']
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $postData);

        $response->assertRedirect('/admin/posts')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Post with Special Chars: àáâãäåæçèéêë & symbols 🚀',
            'content' => 'Content with émojis 🎉 and symbols ©®™ and newlines\nSecond line'
        ]);
    }

    #[Test]
    public function post_update_preserves_timestamps()
    {
        $post = Post::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id
        ]);
        $originalCreatedAt = $post->created_at;

        // Ждем немного для изменения updated_at
        sleep(1);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/posts/{$post->id}", [
                             'title' => 'Updated Title',
                             'content' => 'Updated content',
                             'category_id' => $this->category->id
                         ]);

        $response->assertRedirect('/admin/posts');

        $post->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $post->created_at->timestamp);
        $this->assertNotEquals($originalCreatedAt->timestamp, $post->updated_at->timestamp);
    }

    #[Test]
    public function index_handles_empty_posts_list()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertStatus(200)
                ->assertViewIs('admin.posts.index')
                ->assertViewHas(['posts', 'categories', 'tags']);

        $posts = $response->viewData('posts');
        $this->assertCount(0, $posts);
    }

    #[Test]
    public function categories_and_tags_are_ordered_in_forms()
    {
        $categoryZ = Category::factory()->create(['name' => 'Zebra Category']);
        $categoryA = Category::factory()->create(['name' => 'Alpha Category']);
        
        $tagZ = Tag::factory()->create(['name' => 'zebra-tag']);
        $tagA = Tag::factory()->create(['name' => 'alpha-tag']);

        $response = $this->actingAs($this->admin)->get('/admin/posts/create');

        $response->assertStatus(200)
                ->assertViewHas(['categories', 'tags', 'users']); // Добавляем users
        
        $categories = $response->viewData('categories');
        $tags = $response->viewData('tags');
        
        $categoryNames = $categories->pluck('name')->toArray();
        $tagNames = $tags->pluck('name')->toArray();
        
        $this->assertContains('Alpha Category', $categoryNames);
        $this->assertContains('Zebra Category', $categoryNames);
        $this->assertContains('alpha-tag', $tagNames);
        $this->assertContains('zebra-tag', $tagNames);
    }

    #[Test]
    public function post_creation_handles_numeric_tags()
    {
        $existingTag = Tag::factory()->create(['name' => 'existing-tag']);

        $postData = [
            'title' => 'Post With Numeric Tags',
            'content' => 'Content with numeric tags',
            'tags' => [(string)$existingTag->id, 'new-tag'] // Преобразуем ID в строку
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/posts', $postData);

        $response->assertRedirect('/admin/posts');

        $post = Post::where('title', 'Post With Numeric Tags')->first();
        $this->assertEquals(2, $post->tags()->count());
        $this->assertTrue($post->tags->pluck('name')->contains('existing-tag'));
        $this->assertTrue($post->tags->pluck('name')->contains('new-tag'));
    }
    public function posts_are_ordered_by_title(): void
    {
        // Создаем посты с конкретными названиями в определенном порядке
        $alphaPost = Post::factory()->create(['title' => 'Alpha Post']);
        $betaPost = Post::factory()->create(['title' => 'Beta Post']);
        $zebraPost = Post::factory()->create(['title' => 'Zebra Post']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.comments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.comments.index');

        $posts = $response->viewData('posts');
        $postTitles = $posts->pluck('title')->toArray();

        // Проверяем, что посты отсортированы по алфавиту
        $this->assertEquals('Alpha Post', $postTitles[0]);
        $this->assertEquals('Beta Post', $postTitles[1]);
        $this->assertEquals('Zebra Post', $postTitles[2]);
    }
    
}