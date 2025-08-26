<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'slug'
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}