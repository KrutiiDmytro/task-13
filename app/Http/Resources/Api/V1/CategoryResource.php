<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CategoryResource",
 *     type="object",
 *     title="Category Resource",
 *     description="Ресурс категории для API ответов",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Технологии"),
 *     @OA\Property(property="slug", type="string", example="technologies"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Описание категории"),
 *     @OA\Property(property="posts_count", type="integer", example=5),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="posts", type="array", @OA\Items(ref="#/components/schemas/PostResource"))
 * )
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'posts_count' => $this->when(
                $this->relationLoaded('posts') || isset($this->posts_count),
                $this->posts_count ?? $this->posts->count()
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Включаем посты только при явном запросе и не для XML
            'posts' => $this->when(
                $request->get('format') !== 'xml' && $this->relationLoaded('posts'), 
                function () {
                    return $this->posts->map(function ($post) {
                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'slug' => $post->slug,
                            'tags' => $post->tags->map(function ($tag) {
                                return [
                                    'id' => $tag->id,
                                    'name' => $tag->name,
                                    'slug' => $tag->slug,
                                ];
                            }),
                        ];
                    });
                }
            ),
        ];
    }
}