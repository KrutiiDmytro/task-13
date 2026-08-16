<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'News',
                'description' => 'Release dates, patches and everything else happening in gaming right now.',
            ],
            ['name' => 'Tips', 'description' => 'Short, practical advice to help you play better today.'],
            ['name' => 'Tutorials', 'description' => 'Step-by-step guides, builds and walkthroughs.'],
            ['name' => 'Games', 'description' => 'In-depth reviews and first impressions.'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
            ]);
        }
    }
}
