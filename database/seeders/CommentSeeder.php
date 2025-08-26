<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            ['post_title' => 'Введение в PHP 8.1', 'author' => 'Алексей', 'content' => 'Отличная статья!'],
            ['post_title' => 'Введение в PHP 8.1', 'author' => 'Мария', 'content' => 'Спасибо за объяснение.'],
            ['post_title' => 'Создание REST API с Laravel', 'author' => 'Петр', 'content' => 'Отличный пример!'],
['post_title' => 'Основы JavaScript ES6+', 'author' => 'Анна', 'content' => 'Полезная информация.'],
            // ... остальные комментарии
        ];
        
        foreach ($comments as $commentData) {
            $post = Post::where('title', $commentData['post_title'])->first();

            if ($post) {
                Comment::create([
                    'post_id' => $post->id,
                    'author' => $commentData['author'],
                    'content' => $commentData['content'],
                ]);
            }
        }
    }
}