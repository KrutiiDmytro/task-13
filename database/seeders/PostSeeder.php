<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void // NOSONAR
    {
        $posts = [
            [
                'title' => 'Введение в PHP 8.1',
                'content' => 'PHP 8.1 принес множество новых возможностей, включая readonly properties, enums и улучшенную производительность.',
                'date' => '2024-01-15',
                'category_name' => 'PHP',
                'tags' => ['PHP', 'Программирование'],
            ],
            [
                'title' => 'Создание REST API с Laravel',
                'content' => 'Laravel предоставляет мощные инструменты для создания REST API.',
                'date' => '2024-01-20',
                'category_name' => 'Веб-разработка',
                'tags' => ['PHP', 'Laravel', 'API', 'Веб-разработка'],
            ],
            [
                'title' => 'Основы JavaScript ES6+',
                'content' => 'Современный JavaScript предлагает множество возможностей.',
                'date' => '2024-01-25',
                'category_name' => 'JavaScript',
                'tags' => ['JavaScript', 'Веб-разработка'],
            ],
            [
                'title' => 'Безопасность веб-приложений',
                'content' => 'Защита от SQL-инъекций, XSS атак, CSRF токенов.',
                'date' => '2024-02-01',
                'category_name' => 'Безопасность',
                'tags' => ['Безопасность', 'Веб-разработка'],
            ],
            [
                'title' => 'Основи JavaScript для початківців',
                'content' => 'У цій статті ми розглянемо основи JavaScript: змінні, типи даних, функції та об\'єкти. Це чудовий старт для будь-якого веб-розробника.',
                'date' => '2024-02-10',
                'category_name' => 'JavaScript',
                'tags' => ['JavaScript', 'Програмування', 'Обучение'],
            ],
            [
                'title' => 'Що таке SQL ін\'єкція і як від неї захиститися?',
                'content' => 'SQL-ін\'єкція - це один з найпоширеніших видів атак на веб-додатки. Дізнайтеся, як вона працює і які методи захисту існують в Laravel.',
                'date' => '2024-03-05',
                'category_name' => 'Безопасность',
                'tags' => ['Безопасность', 'Базы данных', 'Laravel'],
            ],
            [
                'title' => 'Корисні інструменти для розробника у 2024 році',
                'content' => 'Огляд популярних інструментів, які спрощують життя розробника: від редакторів коду, таких як VS Code, до систем контролю версій, як Git.',
                'date' => '2024-04-01',
                'category_name' => 'Инструменты',
                'tags' => ['Инструменты', 'Програмування'],
            ],
        ];

        foreach ($posts as $postData) {
            $category = Category::where('name', $postData['category_name'])->first();

            // Получаем случайного пользователя или создаем тестового
            $user = User::first() ?? User::factory()->create();

            $post = Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'date' => $postData['date'],
                'category_id' => $category ? $category->id : null,
                'user_id' => $user->id,
                'author_name' => $user->name,
                'author_email' => $user->email,
            ]);

            $tags = Tag::whereIn('name', $postData['tags'])->get();
            $post->tags()->attach($tags);
        }
    }
}
