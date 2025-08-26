<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;

class PostControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_post_model_minimally(): void
    {
        // Коментар (укр.): простий юніт‑приклад — модель створюється з мінімальними полями
        $post = Post::factory()->create([
            'title'   => 'Unit Post',
            'content' => 'Unit content',
        ]);

        $this->assertDatabaseHas('posts', [
            'id'      => $post->id,
            'title'   => 'Unit Post',
            'content' => 'Unit content',
        ]);
    }
}
