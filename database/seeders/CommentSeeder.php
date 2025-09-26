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
            ['post_title' => 'Введение в PHP 8.1', 'author_name' => 'Алексей', 'author_email' => 'alexey@example.com', 'content' => 'Отличная статья!'],
            ['post_title' => 'Введение в PHP 8.1', 'author_name' => 'Мария', 'author_email' => 'maria@example.com', 'content' => 'Спасибо за объяснение.'],
            ['post_title' => 'Создание REST API с Laravel', 'author_name' => 'Петр', 'author_email' => 'petr@example.com', 'content' => 'Отличный пример!'],
            ['post_title' => 'Основы JavaScript ES6+', 'author_name' => 'Анна', 'author_email' => 'anna@example.com', 'content' => 'Полезная информация.'],
        ];
        
        foreach ($comments as $commentData) {
            $post = Post::where('title', $commentData['post_title'])->first();

            if ($post) {
                Comment::create([
                    'post_id' => $post->id,
                    'author_name' => $commentData['author_name'],
                    'author_email' => $commentData['author_email'],
                    'content' => $commentData['content'],
                ]);
            }
        }
    }
}