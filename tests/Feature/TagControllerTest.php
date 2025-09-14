<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_view_tags_index()
    {
        Tag::factory()->count(3)->create();

        $response = $this->get('/tags');

        $response->assertStatus(200);
        $response->assertViewIs('tags.index');
        $response->assertSee('Теги');
    }

    #[Test]
    public function can_search_tags_by_name()
    {
        Tag::factory()->create(['name' => 'Laravel']);
        Tag::factory()->create(['name' => 'PHP']);
        Tag::factory()->create(['name' => 'JavaScript']);

        $response = $this->get('/tags?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    #[Test]
    public function can_view_tag_with_posts()
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $post = Post::factory()->create();
        $post->tags()->attach($tag);

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $response->assertViewIs('tags.show');
        $response->assertSee('Laravel');
    }

    #[Test]
    public function returns_404_for_non_existent_tag()
    {
        $response = $this->get('/tags/non-existent-tag');

        $response->assertStatus(404);
    }

    #[Test]
    public function tag_index_shows_empty_state_when_no_tags()
    {
        $response = $this->get('/tags');

        $response->assertStatus(200);
        $response->assertSee('Теги не знайдені.');
    }

    #[Test]
    public function tag_search_returns_empty_when_no_matches()
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->get('/tags?search=PHP');

        $response->assertStatus(200);
    }

    #[Test]
    public function tag_search_is_case_insensitive()
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->get('/tags?search=laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    #[Test]
    public function tag_show_page_displays_tag_information()
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    #[Test]
    public function tag_index_pagination_works()
    {
        for ($i = 1; $i <= 25; $i++) {
            Tag::factory()->create(['name' => "Tag{$i}"]);
        }

        $response = $this->get('/tags');

        $response->assertStatus(200);
        $response->assertViewHas('tags');
    }

    #[Test]
    public function tag_show_displays_posts_with_pagination()
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(25)->create();
        $tag->posts()->attach($posts);

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $response->assertViewHas('posts');
    }

    #[Test]
    public function tag_search_preserves_query_string()
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->get('/tags?search=Laravel&page=1');

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    #[Test]
    public function tag_show_displays_empty_posts_when_no_posts()
    {
        $tag = Tag::factory()->create();

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $response->assertSee('Пока нет постов с этим тегом.');
    }

    #[Test]
    public function tag_show_orders_posts_by_latest()
    {
        $tag = Tag::factory()->create();
        $oldPost = Post::factory()->create(['created_at' => now()->subDay()]);
        $newPost = Post::factory()->create(['created_at' => now()]);
        
        $tag->posts()->attach([$oldPost->id, $newPost->id]);

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $posts = $response->viewData('posts');
        $this->assertEquals($newPost->id, $posts->first()->id);
    }

    #[Test]
    public function tag_index_displays_tags_in_alphabetical_order()
    {
        Tag::factory()->create(['name' => 'Zebra']);
        Tag::factory()->create(['name' => 'Alpha']);
        Tag::factory()->create(['name' => 'Beta']);

        $response = $this->get('/tags');

        $response->assertStatus(200);
        $tags = $response->viewData('tags');
        $tagNames = $tags->pluck('name')->toArray();
        
        $this->assertEquals(['Alpha', 'Beta', 'Zebra'], $tagNames);
    }

    #[Test]
    public function tag_search_handles_empty_query()
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->get('/tags?search=');

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    #[Test]
    public function tag_search_handles_whitespace_query()
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->get('/tags?search=' . urlencode('  '));

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    #[Test]
    public function tag_show_handles_tag_with_special_characters()
    {
        $tag = Tag::factory()->create(['name' => 'Laravel & PHP']);

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $response->assertSee('Laravel & PHP');
    }

    #[Test]
    public function tag_show_handles_unicode_tag_names()
    {
        $tag = Tag::factory()->create(['name' => 'Тест']);

        $response = $this->get("/tags/{$tag->slug}");

        $response->assertStatus(200);
        $response->assertSee('Тест');
    }

    #[Test]
    public function tag_search_handles_partial_matches()
    {
        Tag::factory()->create(['name' => 'Laravel']);
        Tag::factory()->create(['name' => 'Laravel Nova']);

        $response = $this->get('/tags?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel');
        $response->assertSee('Laravel Nova');
    }

    #[Test]
    public function tag_search_handles_multiple_words()
    {
        Tag::factory()->create(['name' => 'Laravel Framework']);
        Tag::factory()->create(['name' => 'PHP Framework']);

        $response = $this->get('/tags?search=Framework');

        $response->assertStatus(200);
        $response->assertSee('Laravel Framework');
        $response->assertSee('PHP Framework');
    }

    // Новые тесты для CRUD операций

    #[Test]
    public function can_view_create_tag_form()
    {
        $response = $this->get('/tags/create');

        $response->assertStatus(200);
        $response->assertViewIs('tags.create');
    }

    #[Test]
    public function can_store_new_tag()
    {
        $tagData = [
            'name' => 'New Tag'
        ];

        $response = $this->post('/tags', $tagData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'tag' => [
                'name' => 'New Tag'
            ]
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'New Tag'
        ]);
    }

    #[Test]
    public function store_tag_validates_required_name()
    {
        $response = $this->postJson('/tags', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'errors' => [
                'name' => ['The name field is required.']
            ]
        ]);
    }

    #[Test]
    public function store_tag_validates_unique_name()
    {
        Tag::factory()->create(['name' => 'Existing Tag']);

        $response = $this->postJson('/tags', [
            'name' => 'Existing Tag'
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'errors' => [
                'name' => ['The name has already been taken.']
            ]
        ]);
    }

    #[Test]
    public function store_tag_validates_name_length()
    {
        $response = $this->postJson('/tags', [
            'name' => str_repeat('a', 256)
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'errors' => [
                'name' => ['The name field must not be greater than 255 characters.']
            ]
        ]);
    }

    #[Test]
    public function can_view_edit_tag_form()
    {
        $tag = Tag::factory()->create();

        $response = $this->get("/tags/{$tag->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('tags.edit');
        $response->assertSee($tag->name);
    }

    #[Test]
    public function can_update_tag()
    {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $response = $this->put("/tags/{$tag->id}", [
            'name' => 'New Name'
        ]);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Тег успешно обновлен!');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name'
        ]);
    }

    #[Test]
    public function update_tag_validates_required_name()
    {
        $tag = Tag::factory()->create();

        $response = $this->put("/tags/{$tag->id}", []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function update_tag_validates_unique_name()
    {
        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);

        $response = $this->put("/tags/{$tag2->id}", [
            'name' => 'Tag 1'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function update_tag_allows_same_name()
    {
        $tag = Tag::factory()->create(['name' => 'Same Name']);

        $response = $this->put("/tags/{$tag->id}", [
            'name' => 'Same Name'
        ]);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Тег успешно обновлен!');
    }

    #[Test]
    public function can_delete_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->delete("/tags/{$tag->id}");

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Тег успешно удален!');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id
        ]);
    }

    #[Test]
    public function can_store_tag_via_ajax()
    {
        $tagData = [
            'name' => 'AJAX Tag'
        ];

        $response = $this->postJson('/tags/ajax', $tagData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'tag' => [
                'name' => 'AJAX Tag'
            ]
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'AJAX Tag'
        ]);
    }

    #[Test]
    public function store_ajax_tag_validates_required_name()
    {
        $response = $this->postJson('/tags/ajax', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_ajax_tag_validates_unique_name()
    {
        Tag::factory()->create(['name' => 'Existing AJAX Tag']);

        $response = $this->postJson('/tags/ajax', [
            'name' => 'Existing AJAX Tag'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_ajax_tag_validates_name_length()
    {
        $response = $this->postJson('/tags/ajax', [
            'name' => str_repeat('a', 256)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_ajax_returns_json_response()
    {
        $tagData = [
            'name' => 'JSON Tag'
        ];

        $response = $this->postJson('/tags/ajax', $tagData);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    #[Test]
    public function store_ajax_creates_tag_with_slug()
    {
        $tagData = [
            'name' => 'Tag With Slug'
        ];

        $response = $this->postJson('/tags/ajax', $tagData);

        $response->assertStatus(200);
        
        $tag = Tag::where('name', 'Tag With Slug')->first();
        $this->assertNotNull($tag);
        $this->assertNotNull($tag->slug);
    }
}