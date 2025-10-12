<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_displays_tags_list(): void
    {
        $tags = Tag::factory()->count(3)->create();

        $response = $this->get(route('tags.index'));

        $response->assertStatus(200)
            ->assertViewIs('tags.index')
            ->assertViewHas('tags');
    }

    #[Test]
    public function show_displays_single_tag(): void
    {
        $tag = Tag::factory()->create(['slug' => 'unique-test-tag']);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200)
            ->assertViewIs('tags.show')
            ->assertViewHas('tag', $tag);
    }

    #[Test]
    public function create_displays_form(): void
    {
        $response = $this->get(route('tags.create'));

        $response->assertStatus(200)
            ->assertViewIs('tags.create');
    }

    #[Test]
    public function store_creates_tag_with_valid_data(): void
    {
        $tagData = [
            'name' => 'Unique Test Tag ' . uniqid(), // Делаем имя уникальным
        ];

        $response = $this->post(route('tags.store'), $tagData);

        $response->assertStatus(200) // JSON ответ
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tags', [
            'name' => $tagData['name'],
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->post(route('tags.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_validates_unique_name(): void
    {
        $existingTag = Tag::factory()->create(['name' => 'Existing Tag']);

        $response = $this->post(route('tags.store'), [
            'name' => 'Existing Tag',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function edit_displays_form(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertStatus(200)
            ->assertViewIs('tags.edit')
            ->assertViewHas('tag', $tag);
    }

    #[Test]
    public function update_modifies_tag_with_valid_data(): void
    {
        $tag = Tag::factory()->create();
        $newName = 'Updated Tag Name ' . uniqid();

        $response = $this->put(route('tags.update', $tag), [
            'name' => $newName,
        ]);

        $response->assertRedirect(route('tags.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => $newName,
        ]);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->put(route('tags.update', $tag), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function destroy_deletes_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('tags.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    #[Test]
    public function store_ajax_creates_tag(): void
    {
        $tagData = [
            'name' => 'AJAX Test Tag ' . uniqid(),
        ];

        $response = $this->post(route('tags.store'), $tagData, [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tags', [
            'name' => $tagData['name'],
        ]);
    }

    #[Test]
    public function all_tag_routes_are_accessible(): void
    {
        $tag = Tag::factory()->create(['slug' => 'accessible-test-tag']);

        $routes = [
            route('tags.index') => 200,
            route('tags.create') => 200,
            route('tags.show', $tag->slug) => 200,
            route('tags.edit', $tag) => 200,
        ];

        foreach ($routes as $route => $expectedStatus) {
            $response = $this->get($route);
            $response->assertStatus($expectedStatus, "Route {$route} failed");
        }
    }

    #[Test]
    public function index_supports_search(): void
    {
        $searchableTag = Tag::factory()->create(['name' => 'Searchable Tag']);
        $otherTag = Tag::factory()->create(['name' => 'Other Tag']);

        $response = $this->get(route('tags.index', ['q' => 'Searchable']));

        $response->assertStatus(200)
            ->assertViewIs('tags.index')
            ->assertViewHas('tags');
    }

    #[Test]
    public function show_works_with_slug(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Test Tag for Slug',
            'slug' => 'test-tag-for-slug',
        ]);

        $response = $this->get('/tags/test-tag-for-slug');

        $response->assertStatus(200)
            ->assertViewIs('tags.show')
            ->assertViewHas('tag');
    }
}
