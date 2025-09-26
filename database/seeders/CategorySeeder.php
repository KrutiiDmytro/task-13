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
            ['name' => 'Программирование', 'description' => 'Статьи о программировании'],
            ['name' => 'Веб-разработка', 'description' => 'Создание веб-сайтов'],
            ['name' => 'Безопасность', 'description' => 'Кибербезопасность'],
            ['name' => 'Инструменты', 'description' => 'Обзоры инструментов'],
            ['name' => 'Обучение', 'description' => 'Учебные материалы'],
            ['name' => 'PHP', 'description' => 'Все о PHP'],
            ['name' => 'JavaScript', 'description' => 'Frontend-разработка'],
            ['name' => 'Базы данных', 'description' => 'Работа с СУБД']
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description']
            ]);
        }
    }
}