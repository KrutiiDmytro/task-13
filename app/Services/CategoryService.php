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

    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
