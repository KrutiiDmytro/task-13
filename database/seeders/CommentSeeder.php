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
            [
                'post_title' => 'Steam Deck gets a 90 Hz display in the next hardware revision',
                'author_name' => 'Marcus',
                'author_email' => 'marcus@example.com',
                'content' => 'If battery life really is unchanged, this is an instant upgrade for me.',
            ],
            [
                'post_title' => 'Steam Deck gets a 90 Hz display in the next hardware revision',
                'author_name' => 'Lena',
                'author_email' => 'lena@example.com',
                'content' => 'Waiting for independent benchmarks before I believe the battery claim.',
            ],
            [
                'post_title' => 'Five settings to change before you start any open-world game',
                'author_name' => 'Priya',
                'author_email' => 'priya@example.com',
                'content' => 'The motion blur tip alone made the game playable for me. Thank you.',
            ],
            [
                'post_title' => 'Stop aiming with your wrist',
                'author_name' => 'Dan',
                'author_email' => 'dan@example.com',
                'content' => 'Two days of pain, then everything clicked. Can confirm this works.',
            ],
            [
                'post_title' => 'Hollow echoes: a roguelike that respects your time',
                'author_name' => 'Sofia',
                'author_email' => 'sofia@example.com',
                'content' => 'Eight minute runs are exactly what I need with a toddler in the house.',
            ],
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
