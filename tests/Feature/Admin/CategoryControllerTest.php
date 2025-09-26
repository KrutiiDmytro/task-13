<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Post;

class CategoryControllerTest extends AdminTestCase
{
    public function test_admin_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $this->actingAsAdmin()
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_admin_can_view_create_category_form()
    {
        $this->actingAsAdmin()
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertViewIs('admin.categories.create');
    }

    public function test_admin_can_create_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $this->actingAsAdmin()
            ->post(route('admin.categories.store'), $categoryData)
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);
    }

    public function test_admin_can_show_category()
    {
        $category = Category::factory()->create();

        $this->actingAsAdmin()
            ->get(route('admin.categories.show', $category))
            ->assertOk()
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_admin_can_edit_category()
    {
        $category = Category::factory()->create();

        $this->actingAsAdmin()
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertViewIs('admin.categories.edit')
            ->assertViewHas('category');
    }

    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $this->actingAsAdmin()
            ->put(route('admin.categories.update', $category), $updateData)
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ]);
    }

    public function test_admin_can_delete_empty_category()
    {
        $category = Category::factory()->create();

        $this->actingAsAdmin()
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_posts()
    {
        $category = Category::factory()->create();
        Post::factory()->create(['category_id' => $category->id]);

        $this->actingAsAdmin()
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_creation_validation()
    {
        $this->actingAsAdmin()
            ->post(route('admin.categories.store'), [])
            ->assertSessionHasErrors(['name']);
    }

    public function test_regular_user_cannot_access_categories()
    {
        $this->actingAsRegularUser()
            ->get(route('admin.categories.index'))
            ->assertStatus(403);
    }
}