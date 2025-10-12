<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Отдаём только данные; стандартные meta/links для пагинатора добавит Laravel сам
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Дополнительные данные: только ключи, которых нет в стандартном meta Laravel
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'count' => (int) $this->resource->count(),
                'total_pages' => (int) $this->resource->lastPage(),
            ],
        ];
    }
}
