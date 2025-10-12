<?php

namespace Tests\Unit\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_post_model_minimally(): void
    {
        // Коментар (укр.): простий юніт‑приклад — модель створюється з мінімальними полями
        $post = Post::factory()->create([
            'title' => 'Unit Post',
            'content' => 'Unit content',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Unit Post',
            'content' => 'Unit content',
        ]);
    }
}
