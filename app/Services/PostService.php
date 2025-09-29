<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
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
        $query = Post::with(['category', 'tags', 'user']);

        // Фильтр по поиску (title или content)
        $search = $request->get('q') ?: $request->get('search') ?: $request->get('search_title');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Фильтр по категории (одиночная)
        $category = $request->get('category');
        $categoryId = $request->get('category_id');
        if (!empty($category) || !empty($categoryId)) {
            $categoryValue = $category ?: $categoryId;
            if (is_numeric($categoryValue)) {
                $query->where('category_id', $categoryValue);
            } else {
                // Поиск по slug категории
                $query->whereHas('category', function($q) use ($categoryValue) {
                    $q->where('slug', $categoryValue);
                });
            }
        }

        // Фильтр по категориям (массив)
        $categoryIds = $request->get('category_ids');
        if (!empty($categoryIds) && is_array($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        // Фильтр по тегу (одиночный)
        $tag = $request->get('tag');
        $tagId = $request->get('tag_id');
        if (!empty($tag) || !empty($tagId)) {
            $tagValue = $tag ?: $tagId;
            if (is_numeric($tagValue)) {
                $query->whereHas('tags', function($q) use ($tagValue) {
                    $q->where('tags.id', $tagValue);
                });
            } else {
                // Поиск по slug тега
                $query->whereHas('tags', function($q) use ($tagValue) {
                    $q->where('tags.slug', $tagValue);
                });
            }
        }

        // Фильтр по тегам (массив)
        $tagIds = $request->get('tag_ids');
        if (!empty($tagIds) && is_array($tagIds)) {
            $query->whereHas('tags', function($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        // Сортировка по дате создания (новые сначала)
        $query->orderBy('date', 'desc');

        // Поддержка per_page параметра
        $perPage = min((int) $request->get('per_page', 10), 50);
        return $query->paginate($perPage)->withQueryString();
    }
    
    /**
     * Создает новый пост и привязывает к нему теги.
     */
    public function createPost(array $data): Post
    {
        // Обрабатываем теги (могут быть как строками, так и ID)
        $tagIds = [];
        
        // Обрабатываем tags_text (строка с тегами через запятую)
        if (!empty($data['tags_text']) && is_string($data['tags_text'])) {
            $tagNames = array_map('trim', explode(',', $data['tags_text']));
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => Str::slug($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }
        
        // Обрабатываем tags (массив)
        if (!empty($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagItem) {
                if (is_numeric($tagItem)) {
                    // Если передан ID, используем его
                    $tagIds[] = (int)$tagItem;
                } elseif (is_string($tagItem)) {
                    // Если передана строка, создаем или находим тег
                    $tag = Tag::firstOrCreate(
                        ['name' => trim($tagItem)], // поля для поиска
                        ['slug' => Str::slug(trim($tagItem))] // дополнительные поля при создании
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }

        $data['date'] = now();
        $data['published_at'] = now();
        $post = Post::create($data);

        if (!empty($tagIds)) {
            $post->tags()->attach(array_unique($tagIds)); // убираем дубликаты
        }

        // Загружаем отношения для возврата
        $post->load('tags');

        return $post;
    }

    /**
     * Обновляет существующий пост и его теги.
     */
    public function updatePost(Post $post, array $data): Post
    {
        // Обрабатываем теги (могут быть как строками, так и ID)
        $tagIds = [];
        
        // Обрабатываем tags_text (строка с тегами через запятую)
        if (!empty($data['tags_text']) && is_string($data['tags_text'])) {
            $tagNames = array_map('trim', explode(',', $data['tags_text']));
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => Str::slug($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }
        
        // Обрабатываем tags (массив)
        if (!empty($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagItem) {
                if (is_numeric($tagItem)) {
                    // Если передан ID, используем его
                    $tagIds[] = (int)$tagItem;
                } elseif (is_string($tagItem)) {
                    // Если передана строка, создаем или находим тег
                    $tag = Tag::firstOrCreate(
                        ['name' => trim($tagItem)], // поля для поиска
                        ['slug' => Str::slug(trim($tagItem))] // дополнительные поля при создании
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }

        // Если пост еще не опубликован, публикуем его при обновлении
        if (!isset($data['published_at']) && is_null($post->published_at)) {
            $data['published_at'] = now();
        }

        $post->update($data);

        // Обновляем теги
        if (!empty($tagIds)) {
            $post->tags()->sync(array_unique($tagIds));
        } elseif (array_key_exists('tags', $data) || array_key_exists('tags_text', $data)) {
            // Если теги были переданы, но массив пустой - очищаем все теги
            $post->tags()->detach();
        }

        // Загружаем отношения для возврата
        $post->load('tags');

        return $post;
    }

    /**
     * Удаляет пост и его связи.
     */
    public function deletePost(Post $post): bool
    {
        // Отсоединяем все теги
        $post->tags()->detach();
        
        // Удаляем пост
        return $post->delete();
    }
}