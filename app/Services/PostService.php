<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService
{
    /**
     * Получает отфильтрованные посты с пагинацией.
     */
    public function getFilteredPosts(Request $request): LengthAwarePaginator
    {
        $query = Post::with(['category', 'tags', 'user'])->published();

        $this->applySearchFilter($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applyTagFilter($query, $request);

        $query->orderBy('date', 'desc');

        $perPage = min((int) $request->get('per_page', 10), 50);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Применяет фильтр поиска по title или content
     */
    protected function applySearchFilter(Builder $query, Request $request): void
    {
        $search = $request->get('q') ?: $request->get('search') ?: $request->get('search_title');

        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
        });
    }

    /**
     * Применяет фильтр по категории
     */
    protected function applyCategoryFilter(Builder $query, Request $request): void
    {
        // Одиночная категория
        $category = $request->get('category');
        $categoryId = $request->get('category_id');

        if (! empty($category) || ! empty($categoryId)) {
            $this->applySingleCategoryFilter($query, $category ?: $categoryId);

            return;
        }

        // Множественные категории
        $categoryIds = $request->get('category_ids');
        if (! empty($categoryIds) && is_array($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }
    }

    /**
     * Применяет фильтр по одной категории
     */
    protected function applySingleCategoryFilter(Builder $query, $categoryValue): void
    {
        if (is_numeric($categoryValue)) {
            $query->where('category_id', $categoryValue);

            return;
        }

        // Поиск по slug категории
        $query->whereHas('category', function ($q) use ($categoryValue) {
            $q->where('slug', $categoryValue);
        });
    }

    /**
     * Применяет фильтр по тегам
     */
    protected function applyTagFilter(Builder $query, Request $request): void
    {
        // Одиночный тег
        $tag = $request->get('tag');
        $tagId = $request->get('tag_id');

        if (! empty($tag) || ! empty($tagId)) {
            $this->applySingleTagFilter($query, $tag ?: $tagId);

            return;
        }

        // Множественные теги
        $tagIds = $request->get('tag_ids');
        if (! empty($tagIds) && is_array($tagIds)) {
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }
    }

    /**
     * Применяет фильтр по одному тегу
     */
    protected function applySingleTagFilter(Builder $query, $tagValue): void
    {
        if (is_numeric($tagValue)) {
            $query->whereHas('tags', function ($q) use ($tagValue) {
                $q->where('tags.id', $tagValue);
            });

            return;
        }

        // Поиск по slug тега
        $query->whereHas('tags', function ($q) use ($tagValue) {
            $q->where('tags.slug', $tagValue);
        });
    }

    /**
     * Создает новый пост и привязывает к нему теги.
     */
    public function createPost(array $data): Post
    {
        $tagIds = $this->extractTagIds($data);

        $data['date'] = now();
        $data['published_at'] = now();
        $post = Post::create($data);

        if (! empty($tagIds)) {
            $post->tags()->attach(array_unique($tagIds));
        }

        $post->load('tags');

        return $post;
    }

    /**
     * Обновляет существующий пост и его теги.
     */
    public function updatePost(Post $post, array $data): Post
    {
        $tagIds = $this->extractTagIds($data);

        // Если пост еще не опубликован, публикуем его при обновлении
        if (! isset($data['published_at']) && is_null($post->published_at)) {
            $data['published_at'] = now();
        }

        $post->update($data);

        $this->syncPostTags($post, $tagIds, $data);

        $post->load('tags');

        return $post;
    }

    /**
     * Извлекает ID тегов из данных запроса
     */
    protected function extractTagIds(array $data): array
    {
        $tagIds = [];

        // Обрабатываем tags_text (строка с тегами через запятую)
        if (! empty($data['tags_text']) && is_string($data['tags_text'])) {
            $tagIds = array_merge($tagIds, $this->processTagsText($data['tags_text']));
        }

        // Обрабатываем tags (массив)
        if (! empty($data['tags']) && is_array($data['tags'])) {
            $tagIds = array_merge($tagIds, $this->processTagsArray($data['tags']));
        }

        return $tagIds;
    }

    /**
     * Обрабатывает строку с тегами через запятую
     */
    protected function processTagsText(string $tagsText): array
    {
        $tagIds = [];
        $tagNames = array_map('trim', explode(',', $tagsText));

        foreach ($tagNames as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName)]
            );
            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }

    /**
     * Обрабатывает массив тегов
     */
    protected function processTagsArray(array $tags): array
    {
        $tagIds = [];

        foreach ($tags as $tagItem) {
            if (is_numeric($tagItem)) {
                $tagIds[] = (int) $tagItem;
            } elseif (is_string($tagItem)) {
                $tag = Tag::firstOrCreate(
                    ['name' => trim($tagItem)],
                    ['slug' => Str::slug(trim($tagItem))]
                );
                $tagIds[] = $tag->id;
            }
        }

        return $tagIds;
    }

    /**
     * Синхронизирует теги поста
     */
    protected function syncPostTags(Post $post, array $tagIds, array $data): void
    {
        if (! empty($tagIds)) {
            $post->tags()->sync(array_unique($tagIds));

            return;
        }

        // Если теги были переданы, но массив пустой - очищаем все теги
        if (array_key_exists('tags', $data) || array_key_exists('tags_text', $data)) {
            $post->tags()->detach();
        }
    }

    /**
     * Удаляет пост и его связи.
     */
    public function deletePost(Post $post): bool
    {
        $post->tags()->detach();

        return $post->delete();
    }
}
