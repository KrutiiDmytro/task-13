<?php

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $tagService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = new TagService;
    }

    #[Test]
    public function get_all_returns_all_tags_ordered_by_name(): void
    {
        Tag::factory()->create(['name' => 'Zebra']);
        Tag::factory()->create(['name' => 'Alpha']);
        Tag::factory()->create(['name' => 'Beta']);

        $tags = $this->tagService->getAll();

        $this->assertCount(3, $tags);
        $this->assertEquals('Alpha', $tags->first()->name);
        $this->assertEquals('Zebra', $tags->last()->name);
    }
}
