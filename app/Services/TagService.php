<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    /**
     * Получить все теги.
     */
    public function getAll(): Collection
    {
        return Tag::orderBy('name')->get();
    }
}
