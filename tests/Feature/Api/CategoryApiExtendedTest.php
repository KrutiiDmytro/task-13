<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryApiExtendedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_empty_array_when_no_categories_exist()
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertJson(['data' => []]);
    }

    #[Test]
    public function it_can_create_category_without_description()
    {
        $categoryData = [
            'name' => 'Category Without Description'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Category Without Description')
                ->assertJsonPath('data.description', null);

        $this->assertDatabaseHas('categories', [
            'name' => 'Category Without Description',
            'description' => null
        ]);
    }

    #[Test]
    public function it_validates_name_max_length_when_creating_category()
    {
        $categoryData = [
            'name' => str_repeat('a', 256), // Превышаем лимит в 255 символов
            'description' => 'Valid description'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_can_update_category_partially()
    {
        $category = Category::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description'
        ]);

        // Обновляем только имя
        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Name Only'
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Updated Name Only')
                ->assertJsonPath('data.description', 'Original Description');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name Only',
            'description' => 'Original Description'
        ]);
    }

    #[Test]
    public function it_can_update_category_description_to_null()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Original Description'
        ]);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'description' => null
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.description', null);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'description' => null
        ]);
    }

    #[Test]
    public function it_validates_unique_name_when_updating_category()
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);

        // Попытка обновить category2 с именем category1
        $response = $this->putJson("/api/v1/categories/{$category2->id}", [
            'name' => 'Category 1'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_allows_updating_category_with_same_name()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);

        // Обновляем категорию с тем же именем (должно пройти)
        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Test Category',
            'description' => 'Updated description'
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Test Category')
                ->assertJsonPath('data.description', 'Updated description');
    }

    #[Test]
    public function it_validates_name_max_length_when_updating_category()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => str_repeat('b', 256) // Превышаем лимит
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_returns_404_when_updating_non_existent_category()
    {
        $response = $this->putJson('/api/v1/categories/999', [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_deleting_non_existent_category()
    {
        $response = $this->deleteJson('/api/v1/categories/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_showing_non_existent_category_in_xml()
    {
        $response = $this->get('/api/v1/categories/999?format=xml');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_handle_empty_xml_categories_list()
    {
        $response = $this->get('/api/v1/categories?format=xml');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        // Исправляем: пустой XML имеет структуру <categories/>
        $this->assertStringContainsString('<categories', $response->getContent());
    }

    #[Test]
    public function it_returns_proper_json_structure_for_single_category()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'description'
                        // Убираем created_at и updated_at, так как их нет в CategoryResource
                    ]
                ]);
    }

    #[Test]
    public function it_returns_proper_json_structure_for_categories_collection()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description'
                            // Убираем created_at и updated_at
                        ]
                    ]
                ]);
    }

    #[Test]
    public function it_can_create_category_with_empty_description()
    {
        $categoryData = [
            'name' => 'Category with Empty Description',
            'description' => ''
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Category with Empty Description')
                ->assertJsonPath('data.description', null); // Laravel конвертирует '' в null

        $this->assertDatabaseHas('categories', [
            'name' => 'Category with Empty Description',
            'description' => null // В БД тоже будет null
        ]);
    }

    #[Test]
    public function it_handles_invalid_json_in_create_request()
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => null, // Невалидные данные
            'description' => 12345 // Неправильный тип
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_handles_invalid_json_in_update_request()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => '', // Пустое имя
            'description' => 12345 // Неправильный тип
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_can_handle_special_characters_in_category_name()
    {
        $categoryData = [
            'name' => 'Category with Special Characters: àáâãäåæçèéêë',
            'description' => 'Description with émojis 🚀 and symbols ©®™'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Category with Special Characters: àáâãäåæçèéêë');

        $this->assertDatabaseHas('categories', [
            'name' => 'Category with Special Characters: àáâãäåæçèéêë'
        ]);
    }

    #[Test]
    public function it_returns_correct_content_type_for_json_responses()
    {
        Category::factory()->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');
    }

    #[Test]
    public function it_can_delete_category_that_has_no_posts()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_preserves_timestamps_when_updating_category()
    {
        $category = Category::factory()->create();
        $originalCreatedAt = $category->created_at;

        // Ждем немного, чтобы updated_at изменился
        sleep(1);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200);

        $category->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $category->created_at->timestamp);
        $this->assertNotEquals($originalCreatedAt->timestamp, $category->updated_at->timestamp);
    }

    #[Test]
    public function it_handles_xml_format_with_multiple_categories()
    {
        Category::factory()->create(['name' => 'Category 1']);
        Category::factory()->create(['name' => 'Category 2']);

        $response = $this->get('/api/v1/categories?format=xml');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<categories>', $content);
        $this->assertStringContainsString('Category 1', $content);
        $this->assertStringContainsString('Category 2', $content);
    }

    #[Test]
    public function it_handles_xml_format_for_single_category()
    {
        $category = Category::factory()->create(['name' => 'XML Test Category']);

        $response = $this->get("/api/v1/categories/{$category->id}?format=xml");

        $response->assertStatus(200)
                ->assertHeader('content-type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<category>', $content);
        $this->assertStringContainsString('XML Test Category', $content);
    }

    #[Test]
    public function it_returns_201_status_code_when_creating_category()
    {
        $categoryData = [
            'name' => 'Status Code Test Category',
            'description' => 'Testing status code'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        // Проверяем, что возвращается 201 (Created), а не 200
        $response->assertStatus(201);
    }

    #[Test]
    public function it_can_handle_category_name_with_numbers_and_symbols()
    {
        $categoryData = [
            'name' => 'Category-123 & More!!! (2024)',
            'description' => 'Category with numbers and symbols'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Category-123 & More!!! (2024)');
    }
}