<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * Получить посты с фильтрацией, сортировкой и пагинацией.
     */
    public function getFilteredPosts(Request $request): LengthAwarePaginator
    {
        $query = Post::with(['category', 'tags']);

        // Фильтрация по названию
        if ($request->filled('search_title')) {
            $query->where('title', 'like', '%' . $request->input('search_title') . '%');
        }

        // Фильтрация по категории
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Фильтрация по тегу
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->input('tag_id'));
            });
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Создать новый пост.
     */
    public function createPost(array $validatedData): Post
    {
        $post = Post::create([
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
            'category_id' => $validatedData['category_id'] ?? null,
            'date' => now(), // Устанавливаем текущую дату
        ]);

        if (!empty($validatedData['tags'])) {
            $post->tags()->attach($validatedData['tags']);
        }

        return $post;
    }
}