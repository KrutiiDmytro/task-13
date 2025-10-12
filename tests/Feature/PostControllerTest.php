<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $admin;

    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем тестовые данные
        $this->user = User::factory()->create(['admin' => false]);
        $this->admin = User::factory()->create(['admin' => true]);
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function index_displays_posts_list(): void
    {
        $posts = Post::factory()->count(3)->create();

        $response = $this->get(route('posts.index'));

        $response->assertStatus(200)
            ->assertViewIs('posts.index')
            ->assertViewHas(['posts', 'categories', 'tags']);
    }

    #[Test]
    public function show_displays_single_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertStatus(200)
            ->assertViewIs('posts.show')
            ->assertViewHas('post', $post);
    }

    #[Test]
    public function create_displays_form_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('posts.create'));

        $response->assertStatus(200)
            ->assertViewIs('posts.create')
            ->assertViewHas(['categories', 'tags', 'users']);
    }

    #[Test]
    public function store_redirects_guests_to_login(): void
    {
        // Тест для неавторизованного пользователя
        $response = $this->post(route('posts.store'), []);

        // Гости перенаправляются на страницу логина
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function store_creates_post_with_valid_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => 'Test Post Title',
            'content' => 'This is the content of the test post.',
            'category_id' => $this->category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'content' => 'This is the content of the test post.',
            'category_id' => $this->category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'user_id' => $this->user->id, // Пост должен быть привязан к текущему пользователю
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('posts.store'), []);

        // Для авторизованных пользователей author_name и author_email необязательны
        $response->assertSessionHasErrors(['title', 'content', 'category_id']);
    }

    #[Test]
    public function store_creates_new_tags_from_input(): void
    {
        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => $this->category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'tags' => ['Laravel', 'PHP', 'Testing'],
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', 'Test Post')->first();
        $this->assertCount(3, $post->tags);
    }

    #[Test]
    public function edit_displays_form_for_post_owner(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('posts.edit', $post));

        $response->assertStatus(200)
            ->assertViewIs('posts.edit')
            ->assertViewHas(['post', 'categories', 'tags', 'users']);
    }

    #[Test]
    public function edit_forbids_non_owner_regular_user(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('posts.edit', $post));

        $response->assertStatus(403);
    }

    #[Test]
    public function edit_allows_admin_to_edit_any_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)->get(route('posts.edit', $post));

        $response->assertStatus(200)
            ->assertViewIs('posts.edit');
    }

    #[Test]
    public function update_modifies_post_with_valid_data(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put(route('posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'category_id' => $this->category->id,
            'author_name' => 'Updated Author',
            'author_email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ]);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put(route('posts.update', $post), []);

        // Для авторизованных пользователей author_name и author_email необязательны
        $response->assertSessionHasErrors(['title', 'content', 'category_id']);
    }

    #[Test]
    public function update_forbids_non_owner_regular_user(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->put(route('posts.update', $post), [
            'title' => 'Hacked Title',
            'content' => 'Hacked content',
            'category_id' => $this->category->id,
            'author_name' => 'Hacker',
            'author_email' => 'hacker@example.com',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function destroy_deletes_post_for_owner(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function destroy_deletes_associated_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('images/posts', 'public');

        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'image' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        Storage::disk('public')->assertMissing($path);
    }

    #[Test]
    public function destroy_forbids_non_owner_regular_user(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    #[Test]
    public function guest_cannot_create_posts(): void
    {
        $response = $this->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'Test content',
            'category_id' => $this->category->id,
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function admin_can_manage_any_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        // Admin может редактировать
        $editResponse = $this->actingAs($this->admin)->get(route('posts.edit', $post));
        $editResponse->assertStatus(200);

        // Admin может обновлять
        $updateResponse = $this->actingAs($this->admin)->put(route('posts.update', $post), [
            'title' => 'Admin Updated Title',
            'content' => 'Admin updated content',
            'category_id' => $this->category->id,
            'author_name' => 'Admin',
            'author_email' => 'admin@example.com',
        ]);
        $updateResponse->assertRedirect(route('posts.index'));

        // Admin может удалять
        $deleteResponse = $this->actingAs($this->admin)->delete(route('posts.destroy', $post));
        $deleteResponse->assertRedirect(route('posts.index'));
    }

    #[Test]
    public function store_sets_user_id_from_auth_when_not_provided(): void
    {
        Storage::fake('public');

        $postData = [
            'title' => 'Test Post without user_id',
            'content' => 'Test content',
            'category_id' => $this->category->id,
            'author_name' => 'Test Author',
            'author_email' => 'author@example.com',
            // Намеренно не передаем user_id
        ];

        $response = $this->actingAs($this->user)->post(route('posts.store'), $postData);

        $response->assertRedirect(route('posts.index'));

        // Проверяем, что user_id установлен из auth()->id()
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post without user_id',
            'user_id' => $this->user->id, // Должен быть установлен автоматически
        ]);
    }

    #[Test]
    public function can_manage_post_returns_false_for_guest(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        // Создаем контроллер для прямого тестирования приватного метода
        $controller = new \App\Http\Controllers\PostController(
            app(\App\Services\CategoryService::class),
            app(\App\Services\TagService::class),
            app(\App\Services\PostService::class)
        );

        // Используем рефлексию для доступа к приватному методу
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('canManagePost');
        $method->setAccessible(true);

        // Тестируем без аутентификации (гость)
        $result = $method->invoke($controller, $post);

        $this->assertFalse($result);
    }

    #[Test]
    public function can_manage_post_returns_true_for_admin(): void
    {
        $otherUser = User::factory()->create(['admin' => false]);
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $controller = new \App\Http\Controllers\PostController(
            app(\App\Services\CategoryService::class),
            app(\App\Services\TagService::class),
            app(\App\Services\PostService::class)
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('canManagePost');
        $method->setAccessible(true);

        // Тестируем как админ
        $this->actingAs($this->admin);
        $result = $method->invoke($controller, $post);

        $this->assertTrue($result);
    }

    #[Test]
    public function can_manage_post_returns_true_for_post_owner(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $controller = new \App\Http\Controllers\PostController(
            app(\App\Services\CategoryService::class),
            app(\App\Services\TagService::class),
            app(\App\Services\PostService::class)
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('canManagePost');
        $method->setAccessible(true);

        // Тестируем как владелец поста
        $this->actingAs($this->user);
        $result = $method->invoke($controller, $post);

        $this->assertTrue($result);
    }

    #[Test]
    public function is_admin_returns_true_for_admin_user(): void
    {
        $controller = new \App\Http\Controllers\PostController(
            app(\App\Services\CategoryService::class),
            app(\App\Services\TagService::class),
            app(\App\Services\PostService::class)
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        // Тестируем как админ
        $this->actingAs($this->admin);
        $result = $method->invoke($controller);

        $this->assertTrue($result);
    }

    #[Test]
    public function is_admin_returns_false_for_regular_user(): void
    {
        $controller = new \App\Http\Controllers\PostController(
            app(\App\Services\CategoryService::class),
            app(\App\Services\TagService::class),
            app(\App\Services\PostService::class)
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        // Тестируем как обычный пользователь
        $this->actingAs($this->user);
        $result = $method->invoke($controller);

        $this->assertFalse($result);
    }

    #[Test]
    public function is_admin_returns_false_for_guest(): void
    {
        $controller = new \App\Http\Controllers\PostController(
            app(\App\Services\CategoryService::class),
            app(\App\Services\TagService::class),
            app(\App\Services\PostService::class)
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        // Тестируем без аутентификации
        $result = $method->invoke($controller);

        $this->assertFalse($result);
    }

    #[Test]
    public function store_creates_post_with_image_upload(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('test-post.jpg');

        $postData = [
            'title' => 'Test Post with Image',
            'content' => 'Test content with image',
            'category_id' => $this->category->id,
            'author_name' => 'Test Author',
            'author_email' => 'author@example.com',
            'image' => $image, // Покрывает строки 100-102 в методе store
        ];

        $response = $this->actingAs($this->user)->post(route('posts.store'), $postData);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        // Проверяем, что пост создан
        $post = Post::where('title', 'Test Post with Image')->first();
        $this->assertNotNull($post);

        // Проверяем, что изображение загружено (покрывает строку 101)
        $this->assertNotNull($post->image);
        $this->assertStringContainsString('images/posts/', $post->image);

        // Проверяем, что файл действительно сохранен
        Storage::disk('public')->assertExists($post->image);
    }

    #[Test]
    public function store_auto_fills_author_data_for_authenticated_users(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', 'Test Post')->first();
        $this->assertEquals($this->user->name, $post->author_name);
        $this->assertEquals($this->user->email, $post->author_email);
    }
}
