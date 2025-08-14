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

        // Поиск по названию (оба варианта)
        $search = $request->input('search') ?? $request->input('search_title');
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Категории: поддержка radio (category/category_id) и множественного выбора (category_ids[])
        $categoryIds = (array) ($request->input('category_ids') ?? []);
        $singleCategory = $request->input('category_id') ?? $request->input('category');
        if ($singleCategory) {
            $categoryIds[] = (int) $singleCategory;
        }
        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));
        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        // Теги: поддержка radio (tag/tag_id) и множественного выбора (tag_ids[])
        $tagIds = (array) ($request->input('tag_ids') ?? []);
        $singleTag = $request->input('tag_id') ?? $request->input('tag');
        if ($singleTag) {
            $tagIds[] = (int) $singleTag;
        }
        $tagIds = array_values(array_filter(array_map('intval', $tagIds)));
        if (!empty($tagIds)) {
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

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