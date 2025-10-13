<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        $title = $this->faker->sentence();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->randomNumber(4),
            'content' => $this->faker->paragraphs(3, true),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'date' => $this->faker->date(),
            'author_name' => $this->faker->name(),
            'author_email' => $this->faker->email(),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
