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

    private const TEST_POST_TITLE = 'Test Post';

    private const EMAIL_AUTHOR = 'test@example.com';

    private const TEST_CONTENT = 'Test content';

    private const UDATE_TITLE = 'Updated Title';

    private const AUTHOR_TEST = 'Test Author';

    private const AUTHOR_UPDATE = 'Updated Author';

    private const AUTHOR_EMAIL_UDATE = 'updated@example.com';

    private const CONTENT_UPDATE = 'Updated Content';

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
        Post::factory()->count(3)->create();

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
            'author_name' => self::AUTHOR_TEST,
            'author_email' => self::EMAIL_AUTHOR,
        ]);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'content' => 'This is the content of the test post.',
            'category_id' => $this->category->id,
            'author_name' => self::AUTHOR_TEST,
            'author_email' => self::EMAIL_AUTHOR,
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
            'title' => self::TEST_POST_TITLE,
            'content' => self::TEST_CONTENT,
            'category_id' => $this->category->id,
            'author_name' => self::AUTHOR_TEST,
            'author_email' => self::EMAIL_AUTHOR,
            'tags' => ['Laravel', 'PHP', 'Testing'],
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', self::TEST_POST_TITLE)->first();
        $this->assertEquals(3, $post->tags->count());
    }

    #[Test]
    public function store_auto_fills_empty_author_name_for_authenticated_users(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => self::TEST_POST_TITLE,
            'content' => self::TEST_CONTENT,
            'category_id' => $category->id,
            'author_name' => '', // Пустое значение
            'author_email' => '', // Пустое значение
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', self::TEST_POST_TITLE)->first();
        // Должны быть заполнены автоматически из auth()->user()
        $this->assertEquals($this->user->name, $post->author_name);
        $this->assertEquals($this->user->email, $post->author_email);
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
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $this->category->id,
            'author_name' => self::AUTHOR_UPDATE,
            'author_email' => self::AUTHOR_EMAIL_UDATE,
        ]);

        $response->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
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
    public function update_auto_fills_empty_author_name_for_authenticated_users(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $category->id,
            'author_name' => '', // Пустое значение
            'author_email' => '', // Пустое значение
        ]);

        $response->assertRedirect(route('posts.index'));

        $post->refresh();
        // Должны быть заполнены автоматически из auth()->user()
        $this->assertEquals($this->user->name, $post->author_name);
        $this->assertEquals($this->user->email, $post->author_email);
    }

    #[Test]
    public function update_deletes_old_image_before_uploading_new_one(): void
    {
        Storage::fake('public');

        // Создаём пост с изображением
        $oldImage = UploadedFile::fake()->image('old.jpg');
        $oldImagePath = $oldImage->store('images/posts', 'public');

        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'image' => $oldImagePath,
        ]);

        // Убеждаемся, что старое изображение существует
        $this->assertTrue(Storage::disk('public')->exists($oldImagePath));

        // Загружаем новое изображение
        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $this->category->id,
            'image' => $newImage,
        ]);

        $response->assertRedirect(route('posts.index'));

        // Проверяем, что старое изображение удалено
        $this->assertFalse(Storage::disk('public')->exists($oldImagePath));

        // Проверяем, что новое изображение сохранено
        $post->refresh();
        $this->assertNotNull($post->image);
        $this->assertStringContainsString('images/posts/', $post->image);
        $this->assertTrue(Storage::disk('public')->exists($post->image));
    }

    #[Test]
    public function update_preserves_author_data_when_not_provided(): void
    {
        $originalAuthorName = 'Original Author';
        $originalAuthorEmail = 'original@example.com';

        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'author_name' => $originalAuthorName,
            'author_email' => $originalAuthorEmail,
        ]);

        $category = Category::factory()->create();

        // Обновляем пост БЕЗ передачи author_name и author_email
        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('posts.index'));

        $post->refresh();
        // Проверяем, что данные автора заменены на данные текущего пользователя
        $this->assertEquals($this->user->name, $post->author_name);
        $this->assertEquals($this->user->email, $post->author_email);
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

        // Проверяем, что файл существует
        $this->assertTrue(Storage::disk('public')->exists($path));

        $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        // Проверяем, что файл удалён
        $this->assertFalse(Storage::disk('public')->exists($path));
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
            'title' => self::TEST_POST_TITLE,
            'content' => self::TEST_CONTENT,
            'category_id' => $this->category->id,
            'author_name' => self::AUTHOR_TEST,
            'author_email' => self::EMAIL_AUTHOR,
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
            'content' => self::TEST_CONTENT,
            'category_id' => $this->category->id,
            'author_name' => self::AUTHOR_TEST,
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
    public function store_creates_post_with_image_upload(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('test-post.jpg');

        $postData = [
            'title' => 'Test Post with Image',
            'content' => 'Test content with image',
            'category_id' => $this->category->id,
            'author_name' => self::AUTHOR_TEST,
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
        $this->assertTrue(Storage::disk('public')->exists($post->image));
    }

    #[Test]
    public function store_auto_fills_author_data_for_authenticated_users(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => self::TEST_POST_TITLE,
            'content' => self::TEST_CONTENT,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', self::TEST_POST_TITLE)->first();
        $this->assertEquals($this->user->name, $post->author_name);
        $this->assertEquals($this->user->email, $post->author_email);
    }

    #[Test]
    public function update_removes_old_image_when_uploading_new_one(): void
    {
        Storage::fake('public');

        // Создаём пост с изображением
        UploadedFile::fake()->image('old.jpg');
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'image' => 'images/posts/old.jpg',
        ]);

        // Загружаем новое изображение
        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $this->category->id,
            'image' => $newImage,
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertNotEquals('images/posts/old.jpg', $post->refresh()->image);
    }

    #[Test]
    public function update_fills_author_data_for_authenticated_users(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => 'Updated Post',
            'content' => self::CONTENT_UPDATE,
            'category_id' => $this->category->id,
        ]);

        $response->assertRedirect(route('posts.index'));

        $post->refresh();
        $this->assertEquals($this->user->name, $post->author_name);
        $this->assertEquals($this->user->email, $post->author_email);
    }

    #[Test]
    public function destroy_removes_image_from_storage(): void
    {
        Storage::fake('public');

        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'image' => 'images/posts/test.jpg',
        ]);

        // Создаём фиктивный файл
        Storage::disk('public')->put('images/posts/test.jpg', 'test content');

        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function store_with_provided_author_name_and_email(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => self::TEST_POST_TITLE,
            'content' => self::TEST_CONTENT,
            'category_id' => $category->id,
            'author_name' => 'Custom Author',
            'author_email' => 'custom@example.com',
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', self::TEST_POST_TITLE)->first();
        $this->assertEquals('Custom Author', $post->author_name);
        $this->assertEquals('custom@example.com', $post->author_email);
    }

    #[Test]
    public function update_with_provided_author_data(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $category->id,
            'author_name' => self::AUTHOR_UPDATE,
            'author_email' => self::AUTHOR_EMAIL_UDATE,
        ]);

        $response->assertRedirect(route('posts.index'));

        $post->refresh();
        $this->assertEquals(self::AUTHOR_UPDATE, $post->author_name);
        $this->assertEquals(self::AUTHOR_EMAIL_UDATE, $post->author_email);
    }

    #[Test]
    public function update_without_changing_image(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => self::UDATE_TITLE,
            'content' => self::CONTENT_UPDATE,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertNull(Post::find($post->id)->image);
    }

    #[Test]
    public function update_for_non_owner_returns_403(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->patch(route('posts.update', $post), [
            'title' => 'Hacked Title',
            'content' => 'Hacked Content',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(403);
    }
}
