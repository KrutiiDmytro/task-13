<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Получить все категории.
     */
    public function getAll(): Collection
    {
        return Category::orderBy('name')->get();
    }
}