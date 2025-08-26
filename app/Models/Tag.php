<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Зв’язок багато-до-багатьох з постами.
     *  Дозволяє отримати всі пости, до яких прив’язаний тег.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    /**
     * Автоматичне встановлення slug, якщо не переданий явно.
     *  Якщо при створенні/оновленні тегу slug порожній — згенеруємо зі 'name'.
     */
    protected static function booted()
    {
        static::saving(function (Tag $tag) {
            if (empty($tag->slug) && !empty($tag->name)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
