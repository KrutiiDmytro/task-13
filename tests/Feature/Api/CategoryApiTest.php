<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
public function it_can_get_list_of_categories_in_json()
{
    Category::factory()->create(['name' => 'Category 1']);
    Category::factory()->create(['name' => 'Category 2']);
    Category::factory()->create(['name' => 'Category 3']);

    $response = $this->getJson('/api/v1/categories');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_get_list_of_categories_in_xml()
    {
        Category::factory()->create(['name' => 'Test Category']);

        $response = $this->get('/api/v1/categories?format=xml');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $this->assertStringContainsString('<categories>', $response->getContent());
        $this->assertStringContainsString('Test Category', $response->getContent());
    }

    #[Test]
    public function it_can_create_new_category()
    {
        $categoryData = [
            'name' => 'New Category',
            'description' => 'Category description'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'New Category')
                ->assertJsonPath('data.description', 'Category description');

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'description' => 'Category description'
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_category()
    {
        $response = $this->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_validates_unique_name_when_creating_category()
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $categoryData = [
            'name' => 'Existing Category',
            'description' => 'Some description'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_can_get_specific_category()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.id', $category->id)
                ->assertJsonPath('data.name', 'Test Category')
                ->assertJsonPath('data.description', 'Test Description');
    }

    #[Test]
    public function it_can_get_specific_category_in_xml_format()
    {
        $category = Category::factory()->create(['name' => 'XML Category']);

        $response = $this->get("/api/v1/categories/{$category->id}?format=xml");

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $this->assertStringContainsString('<category>', $response->getContent());
        $this->assertStringContainsString('XML Category', $response->getContent());
    }

    #[Test]
    public function it_can_update_existing_category()
    {
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/v1/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Updated Category')
                ->assertJsonPath('data.description', 'Updated description');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);
    }

    #[Test]
    public function it_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    #[Test]
    public function it_returns_404_when_category_not_found()
    {
        $response = $this->getJson('/api/v1/categories/999');

        $response->assertStatus(404);
    }
}