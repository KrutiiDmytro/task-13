<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PostResource",
 *     type="object",
 *     title="Post Resource",
 *     description="Ресурс поста для API ответов",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Заголовок поста"),
 *     @OA\Property(property="content", type="string", example="Содержание поста"),
 *     @OA\Property(property="date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="image", type="string", nullable=true, example="images/post.jpg"),
 *     @OA\Property(property="author_name", type="string", nullable=true, example="Имя автора"),
 *     @OA\Property(property="author_email", type="string", nullable=true, example="author@example.com"),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="category", ref="#/components/schemas/CategoryResource"),
 *     @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/TagResource")),
 *     @OA\Property(property="comments", type="array", @OA\Items(ref="#/components/schemas/CommentResource"))
 * )
 */
class PostResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'date' => $this->date?->format('Y-m-d'),
            'image' => $this->image,
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        // Включаем связанные данные только если они загружены
        if ($this->relationLoaded('category')) {
            $data['category'] = new CategoryResource($this->category);
        }

        if ($this->relationLoaded('tags')) {
            $data['tags'] = TagResource::collection($this->tags);
        }

        if ($this->relationLoaded('comments')) {
            $data['comments'] = CommentResource::collection($this->comments);
        }

        return $data;
    }
}
