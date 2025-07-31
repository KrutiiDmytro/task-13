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