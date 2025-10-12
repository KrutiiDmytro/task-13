<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommentResource",
 *     type="object",
 *     title="Comment Resource",
 *     description="Ресурс комментария для API ответов",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="content", type="string", example="Содержание комментария"),
 *     @OA\Property(property="author_name", type="string", example="Имя автора"),
 *     @OA\Property(property="author_email", type="string", example="author@example.com"),
 *     @OA\Property(property="post_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'content' => $this->content,
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'post_id' => $this->post_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if ($this->relationLoaded('post')) {
            $data['post'] = new PostResource($this->post);
        }

        return $data;
    }
}
