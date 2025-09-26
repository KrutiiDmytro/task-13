<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Comment",
 *     type="object",
 *     title="Comment",
 *     description="Модель комментария к посту",
 *     required={"id", "content", "post_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Уникальный идентификатор комментария",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Содержание комментария",
 *         example="Отличная статья! Очень полезная информация."
 *     ),
 *     @OA\Property(
 *         property="author_name",
 *         type="string",
 *         maxLength=255,
 *         description="Имя автора комментария",
 *         example="Петр Петров"
 *     ),
 *     @OA\Property(
 *         property="author_email",
 *         type="string",
 *         format="email",
 *         description="Email автора комментария",
 *         example="petr@example.com"
 *     ),
 *     @OA\Property(
 *         property="post_id",
 *         type="integer",
 *         format="int64",
 *         description="ID поста, к которому относится комментарий",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата создания комментария",
 *         example="2023-12-01T10:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата последнего обновления комментария",
 *         example="2023-12-01T15:30:00.000000Z"
 *     )
 * )
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'author_name',
        'author_email',
        'post_id',
        'user_id',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}