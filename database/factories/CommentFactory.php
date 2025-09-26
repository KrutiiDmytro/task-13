<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(),
            'author_name' => fake()->name(),
            'author_email' => fake()->email(),
            'post_id' => Post::factory()
        ];
    }
}