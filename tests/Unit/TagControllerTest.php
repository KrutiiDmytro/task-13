<?php

namespace Tests\Unit;

use App\Http\Controllers\TagController;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    private TagController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TagController();
    }

    #[Test]
    public function store_ajax_creates_tag_with_valid_data(): void
    {
        $request = Request::create('/tags/ajax', 'POST', [
            'name' => 'AJAX Test Tag',
        ]);

        $response = $this->controller->storeAjax($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('AJAX Test Tag', $data['tag']['name']);
        $this->assertArrayHasKey('id', $data['tag']);

        $this->assertDatabaseHas('tags', [
            'name' => 'AJAX Test Tag',
        ]);
    }

    #[Test]
    public function store_ajax_returns_validation_error_for_empty_name(): void
    {
        $request = Request::create('/tags/ajax', 'POST', []);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->storeAjax($request);
    }

    #[Test]
    public function store_ajax_returns_validation_error_for_duplicate_name(): void
    {
        Tag::factory()->create(['name' => 'Existing AJAX Tag']);

        $request = Request::create('/tags/ajax', 'POST', [
            'name' => 'Existing AJAX Tag',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->storeAjax($request);
    }

    #[Test]
    public function store_ajax_returns_validation_error_for_too_long_name(): void
    {
        $longName = str_repeat('a', 256); // больше 255 символов

        $request = Request::create('/tags/ajax', 'POST', [
            'name' => $longName,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->storeAjax($request);
    }

    #[Test]
    public function store_ajax_creates_tag_with_auto_generated_slug(): void
    {
        $request = Request::create('/tags/ajax', 'POST', [
            'name' => 'AJAX Tag With Spaces',
        ]);

        $response = $this->controller->storeAjax($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('AJAX Tag With Spaces', $data['tag']['name']);

        // Проверяем, что slug был автоматически сгенерирован
        $tag = Tag::where('name', 'AJAX Tag With Spaces')->first();
        $this->assertNotNull($tag);
        $this->assertNotEmpty($tag->slug);
        $this->assertNotNull($data['tag']['id']);
    }

    #[Test]
    public function store_ajax_validates_unique_name(): void
    {
        // Создаем тег
        Tag::factory()->create(['name' => 'Unique Tag']);

        $request = Request::create('/tags/ajax', 'POST', [
            'name' => 'Unique Tag',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->storeAjax($request);
    }

    #[Test]
    public function store_ajax_validates_required_name(): void
    {
        $request = Request::create('/tags/ajax', 'POST', [
            'name' => '',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->storeAjax($request);
    }

    #[Test]
    public function store_ajax_validates_max_length(): void
    {
        $request = Request::create('/tags/ajax', 'POST', [
            'name' => str_repeat('x', 256),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->storeAjax($request);
    }
}
