<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'RPG'], ['name' => 'FPS'], ['name' => 'Indie'],
            ['name' => 'Strategy'], ['name' => 'Roguelike'], ['name' => 'Speedrun'],
            ['name' => 'Multiplayer'], ['name' => 'Co-op'], ['name' => 'Esports'],
            ['name' => 'PC'], ['name' => 'PlayStation'], ['name' => 'Xbox'],
            ['name' => 'Nintendo Switch'], ['name' => 'Steam Deck'], ['name' => 'VR'],
            ['name' => 'Modding'], ['name' => 'Patch Notes'], ['name' => 'Beginner'],
            ['name' => 'Boss Fight'], ['name' => 'Open World'], ['name' => 'Performance'],
            ['name' => 'Review'],
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag['name'],
                'slug' => Str::slug($tag['name']),
            ]);
        }
    }
}
