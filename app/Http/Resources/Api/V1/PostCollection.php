<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => (int) $this->resource->total(),
                'count' => (int) $this->resource->count(),
                'per_page' => (int) $this->resource->perPage(),
                'current_page' => (int) $this->resource->currentPage(),
                'total_pages' => (int) $this->resource->lastPage(),
            ],
        ];
    }
}