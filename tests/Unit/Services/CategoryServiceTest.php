<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService;
    }

    public function test_get_all_returns_categories_sorted_by_name()
    {
        // Создаем категории в случайном порядке
        Category::factory()->create(['name' => 'Zebra', 'slug' => Str::slug('Zebra')]);
        Category::factory()->create(['name' => 'Alpha', 'slug' => Str::slug('Alpha')]);
        Category::factory()->create(['name' => 'Beta', 'slug' => Str::slug('Beta')]);

        $categories = $this->categoryService->getAll();

        // Проверяем, что категории отсортированы по имени
        $this->assertEquals(3, $categories->count());
        $this->assertEquals('Alpha', $categories->first()->name);
        $this->assertEquals('Zebra', $categories->last()->name);
    }

    public function test_get_all_returns_empty_collection_when_no_categories()
    {
        $categories = $this->categoryService->getAll();

        $this->assertEquals(0, $categories->count());
        $this->assertTrue($categories->isEmpty());
    }

    public function test_get_all_includes_category_details()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description',
        ]);

        $categories = $this->categoryService->getAll();

        $this->assertEquals(1, $categories->count());
        $retrievedCategory = $categories->first();
        $this->assertEquals('Test Category', $retrievedCategory->name);
        $this->assertEquals('test-category', $retrievedCategory->slug);
        $this->assertEquals('Test Description', $retrievedCategory->description);
    }
}
