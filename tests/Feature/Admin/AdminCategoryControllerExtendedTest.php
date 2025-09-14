<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminCategoryControllerExtendedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    #[Test]
    public function admin_can_view_categories_index_with_pagination()
    {
        // Создаем 20 категорий с уникальными именами для избежания конфликтов
        for ($i = 1; $i <= 20; $i++) {
            Category::factory()->create([
                'name' => "Category {$i}",
                'description' => "Description for category {$i}"
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200)
                ->assertViewIs('admin.categories.index')
                ->assertViewHas('categories');

        $categories = $response->viewData('categories');
        $this->assertCount(15, $categories); // Пагинация по 15
        $this->assertTrue($categories->hasPages());
    }
    #[Test]
    public function categories_index_shows_post_count_for_each_category()
    {
        $category1 = Category::factory()->create(['name' => 'Test Category A']);
        $category2 = Category::factory()->create(['name' => 'Test Category B']);
        
        // Создаем посты для категорий
        Post::factory()->count(3)->create(['category_id' => $category1->id]);
        Post::factory()->count(5)->create(['category_id' => $category2->id]);

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        
        $categories = $response->viewData('categories');
        $categoryData = $categories->items();
        
        // Проверяем, что загружен счетчик постов
        $this->assertEquals(3, $categoryData[0]->posts_count);
        $this->assertEquals(5, $categoryData[1]->posts_count);
    }

    #[Test]
    public function categories_are_sorted_by_name_in_index()
    {
        Category::factory()->create(['name' => 'Zebra Category Unique']);
        Category::factory()->create(['name' => 'Alpha Category Unique']);
        Category::factory()->create(['name' => 'Beta Category Unique']);

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        
        $categories = $response->viewData('categories');
        $categoryNames = $categories->pluck('name')->toArray();
        
        $this->assertEquals(['Alpha Category Unique', 'Beta Category Unique', 'Zebra Category Unique'], $categoryNames);
    }
    #[Test]
    public function admin_can_view_create_category_form()
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/create');

        $response->assertStatus(200)
                ->assertViewIs('admin.categories.create');
    }

    #[Test]
    public function regular_user_cannot_access_create_category_form()
    {
        $response = $this->actingAs($this->user)->get('/admin/categories/create');

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_create_category_form()
    {
        $response = $this->get('/admin/categories/create');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function admin_can_create_category_with_all_fields()
    {
        $categoryData = [
            'name' => 'New Test Category',
            'description' => 'This is a test category description'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $categoryData);

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success', 'Категория успешно создана!');

        $this->assertDatabaseHas('categories', $categoryData);
    }

    #[Test]
    public function admin_can_create_category_without_description()
    {
        $categoryData = [
            'name' => 'Category Without Description'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $categoryData);

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Category Without Description',
            'description' => null
        ]);
    }

    #[Test]
    public function category_creation_validates_required_name()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', []);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function category_creation_validates_unique_name()
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', [
                             'name' => 'Existing Category',
                             'description' => 'Some description'
                         ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertEquals(1, Category::where('name', 'Existing Category')->count());
    }

    #[Test]
    public function category_creation_validates_name_max_length()
    {
        $longName = str_repeat('a', 256); // Превышаем лимит в 255 символов

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', [
                             'name' => $longName,
                             'description' => 'Valid description'
                         ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function category_creation_validates_description_max_length()
    {
        $longDescription = str_repeat('a', 501); // Превышаем лимит в 500 символов

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', [
                             'name' => 'Valid Name',
                             'description' => $longDescription
                         ]);

        $response->assertSessionHasErrors(['description']);
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function admin_can_view_category_details()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);

        // Создаем несколько постов для категории
        Post::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
                         ->get("/admin/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertViewIs('admin.categories.show')
                ->assertViewHas('category');

        $viewCategory = $response->viewData('category');
        $this->assertEquals($category->id, $viewCategory->id);
        $this->assertTrue($viewCategory->relationLoaded('posts'));
        $this->assertCount(3, $viewCategory->posts);
    }

    #[Test]
    public function admin_can_view_edit_category_form()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
                         ->get("/admin/categories/{$category->id}/edit");

        $response->assertStatus(200)
                ->assertViewIs('admin.categories.edit')
                ->assertViewHas('category', $category);
    }

    #[Test]
    public function admin_can_update_category()
    {
        $category = Category::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ];

        $response = $this->actingAs($this->admin)
                         ->put("/admin/categories/{$category->id}", $updateData);

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success', 'Категория успешно обновлена!');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    #[Test]
    public function admin_can_update_category_with_same_name()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/categories/{$category->id}", [
                             'name' => 'Test Category', // То же имя
                             'description' => 'Updated Description'
                         ]);

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Test Category',
            'description' => 'Updated Description'
        ]);
    }

    #[Test]
    public function category_update_validates_unique_name_except_current()
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/categories/{$category2->id}", [
                             'name' => 'Category 1' // Имя уже существует
                         ]);

        $response->assertSessionHasErrors(['name']);

        // Проверяем, что category2 не изменилась
        $this->assertDatabaseHas('categories', [
            'id' => $category2->id,
            'name' => 'Category 2'
        ]);
    }

    #[Test]
    public function admin_can_delete_category_without_posts()
    {
        $category = Category::factory()->create(['name' => 'Empty Category']);

        $response = $this->actingAs($this->admin)
                         ->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success', 'Категория успешно удалена!');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[Test]
    public function admin_cannot_delete_category_with_posts()
    {
        $category = Category::factory()->create(['name' => 'Category With Posts']);
        Post::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
                         ->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('error', 'Нельзя удалить категорию с постами!');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function returns_404_when_accessing_non_existent_category()
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_editing_non_existent_category()
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/999/edit');

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_updating_non_existent_category()
    {
        $response = $this->actingAs($this->admin)
                         ->put('/admin/categories/999', [
                             'name' => 'Updated Name'
                         ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_when_deleting_non_existent_category()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/categories/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function regular_user_cannot_create_category()
    {
        $response = $this->actingAs($this->user)
                         ->post('/admin/categories', [
                             'name' => 'Test Category'
                         ]);

        $response->assertStatus(403);
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function regular_user_cannot_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
                         ->put("/admin/categories/{$category->id}", [
                             'name' => 'Updated Name'
                         ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function regular_user_cannot_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
                         ->delete("/admin/categories/{$category->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function guest_cannot_create_category()
    {
        $response = $this->post('/admin/categories', [
            'name' => 'Test Category'
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseEmpty('categories');
    }

    #[Test]
    public function guest_cannot_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->put("/admin/categories/{$category->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function guest_cannot_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function admin_can_handle_special_characters_in_category_name()
    {
        $categoryData = [
            'name' => 'Category with Special Chars: àáâãäåæçèéêë & symbols 🚀',
            'description' => 'Description with émojis 🎉 and symbols ©®™'
        ];

        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', $categoryData);

        $response->assertRedirect('/admin/categories')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', $categoryData);
    }

    #[Test]
    public function category_creation_handles_empty_description_as_null()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/admin/categories', [
                             'name' => 'Test Category',
                             'description' => ''
                         ]);

        $response->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => null
        ]);
    }

    #[Test]
    public function category_update_preserves_timestamps()
    {
        $category = Category::factory()->create(['name' => 'Original Name']);
        $originalCreatedAt = $category->created_at;

        // Ждем немного для изменения updated_at
        sleep(1);

        $response = $this->actingAs($this->admin)
                         ->put("/admin/categories/{$category->id}", [
                             'name' => 'Updated Name'
                         ]);

        $response->assertRedirect('/admin/categories');

        $category->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $category->created_at->timestamp);
        $this->assertNotEquals($originalCreatedAt->timestamp, $category->updated_at->timestamp);
    }

    #[Test]
    public function index_handles_empty_categories_list()
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200)
                ->assertViewIs('admin.categories.index')
                ->assertViewHas('categories');

        $categories = $response->viewData('categories');
        $this->assertCount(0, $categories);
    }
}