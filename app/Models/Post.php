<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    
    // (UA) Дозволені для масового присвоєння поля
    protected $fillable = [
        'title',
        'content',
        'date',
        'image',
        'category_id',
        'user_id',
        'author_name',
        'author_email',
    ];

    // (UA) Кастинг типів
    protected $casts = [
        'date' => 'date',
    ];

    // (UA) Зв'язок з категорією
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // (UA) Багато-до-багатьох з тегами
    public function tags()
    {
        return $this->belongsToMany(\App\Models\Tag::class);
    }

    // (UA) Коментарі до поста
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // (UA) Автор поста (користувач)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
