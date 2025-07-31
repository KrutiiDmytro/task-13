<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

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
            Category::create($category);
        }
    }
}