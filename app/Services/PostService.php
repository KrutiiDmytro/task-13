<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * Получает посты с фильтрацией и пагинацией.
     */
    public function getFilteredPosts(Request $request): LengthAwarePaginator
    {
        $query = Post::with('category', 'tags')->latest('date');

        // Поиск по названию
        if ($request->filled('search_title')) {
            $query->where('title', 'like', '%' . $request->search_title . '%');
        }

        // Фильтр по категории
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Фильтр по тегу
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        // Пагинация (по 10 постов на страницу)
        return $query->paginate(10)->withQueryString();
    }

    /**
     * Создает новый пост и привязывает к нему теги.
     */
    public function createPost(array $data): Post
    {
        $data['date'] = now();
        $post = Post::create($data);

        if (!empty($data['tags'])) {
            $post->tags()->attach($data['tags']);
        }

        return $post;
    }

    /**
     * Обновляет существующий пост и его теги.
     */
    public function updatePost(Post $post, array $data): Post
    {
        $post->update($data);

        // sync() - удобный метод для обновления связей "многие-ко-многим"
        // Он удалит старые теги и добавит новые.
        $post->tags()->sync($data['tags'] ?? []);

        return $post;
    }
}