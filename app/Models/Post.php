<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     title="Post",
 *     description="Модель поста блога",
 *     required={"id", "title", "content", "category_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Уникальный идентификатор поста",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         description="Заголовок поста",
 *         example="Как создать REST API с Laravel"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Полное содержание поста",
 *         example="Подробное руководство по созданию REST API..."
 *     ),
 *     @OA\Property(
 *         property="date",
 *         type="string",
 *         format="date",
 *         description="Дата поста",
 *         example="2023-12-01"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         nullable=true,
 *         description="URL изображения поста",
 *         example="images/post-image.jpg"
 *     ),
 *     @OA\Property(
 *         property="author_name",
 *         type="string",
 *         nullable=true,
 *         maxLength=255,
 *         description="Имя автора поста",
 *         example="Иван Иванов"
 *     ),
 *     @OA\Property(
 *         property="author_email",
 *         type="string",
 *         format="email",
 *         nullable=true,
 *         description="Email автора поста",
 *         example="ivan@example.com"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID пользователя-автора",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         type="integer",
 *         format="int64",
 *         description="ID категории поста",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата создания",
 *         example="2023-12-01T10:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата последнего обновления",
 *         example="2023-12-01T15:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         ref="#/components/schemas/Category",
 *         description="Категория поста"
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         description="Теги поста",
 *         @OA\Items(ref="#/components/schemas/Tag")
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="array",
 *         description="Комментарии к посту",
 *         @OA\Items(ref="#/components/schemas/Comment")
 *     )
 * )
 */
class Post extends Model
{
    use HasFactory;
    
    //  Дозволені для масового присвоєння поля
    protected $fillable = [
        'title',
        'content',
        'date',
        'image',
        'category_id',
        'user_id',
        'author_name',
        'author_email',
        'slug',          // Добавляем slug
        'published_at',  // Добавляем published_at
    ];

    protected $casts = [
        'date' => 'date',
        'published_at' => 'datetime',  // Добавляем cast для published_at
    ];

    // Автоматическая генерация slug
    protected static function booted()
    {
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
                
                // Проверяем уникальность slug
                $originalSlug = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->exists()) {
                    $post->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
                
                // Проверяем уникальность slug
                $originalSlug = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->where('id', '!=', $post->id)->exists()) {
                    $post->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    // Добавляем scope для опубликованных постов
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    // Добавляем scope для поиска
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%");
        });
    }

    //  Зв'язок з категорією
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Багато-до-багатьох з тегами
    public function tags()
    {
        return $this->belongsToMany(\App\Models\Tag::class);
    }

    // Коментарі до поста
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    //  Автор поста (користувач)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}