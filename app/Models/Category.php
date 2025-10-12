<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     description="Модель категории постов",
 *     required={"id", "name", "slug"},
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Уникальный идентификатор категории",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Название категории",
 *         example="Технологии"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         maxLength=255,
 *         description="URL-дружественный идентификатор категории",
 *         example="technologies"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         maxLength=500,
 *         description="Описание категории",
 *         example="Статьи о современных технологиях и разработке"
 *     ),
 *     @OA\Property(
 *         property="posts_count",
 *         type="integer",
 *         description="Количество постов в категории",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата создания категории",
 *         example="2023-12-01T10:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата последнего обновления категории",
 *         example="2023-12-01T15:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="posts",
 *         type="array",
 *         description="Посты в категории (загружается по запросу)",
 *
 *         @OA\Items(ref="#/components/schemas/Post")
 *     )
 * )
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'slug'];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (Category $category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
