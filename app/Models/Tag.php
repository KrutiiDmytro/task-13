<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     title="Tag",
 *     description="Модель тега",
 *     required={"id", "name", "slug"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Уникальный идентификатор тега",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Название тега",
 *         example="PHP"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         maxLength=255,
 *         description="URL-дружественный идентификатор тега",
 *         example="php"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата создания тега",
 *         example="2023-12-01T10:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата последнего обновления тега",
 *         example="2023-12-01T15:30:00.000000Z"
 *     )
 * )
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug'];

    protected static function booted(): void
    {
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function posts()
    {
        return $this->belongsToMany(\App\Models\Post::class, 'post_tag', 'tag_id', 'post_id');
    }
}
