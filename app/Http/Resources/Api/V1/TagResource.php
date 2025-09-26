<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TagResource",
 *     type="object",
 *     title="Tag Resource",
 *     description="Ресурс тега для API ответов",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="PHP"),
 *     @OA\Property(property="slug", type="string", example="php"),
 *     @OA\Property(property="posts_count", type="integer", example=12),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="posts", type="array", @OA\Items(ref="#/components/schemas/PostResource"))
 * )
 */
class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'posts_count' => $this->when(
                isset($this->posts_count) || $this->relationLoaded('posts'),
                $this->posts_count ?? ($this->relationLoaded('posts') ? $this->posts->count() : 0)
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        // Включаем посты только при явном запросе
        if ($this->relationLoaded('posts')) {
            $data['posts'] = PostResource::collection($this->posts);
        }

        return $data;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
                'format' => $request->get('format', 'json'),
            ],
        ];
    }
}