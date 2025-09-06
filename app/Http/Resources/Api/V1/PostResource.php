<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'date' => $this->date,
            'image' => $this->image,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Добавляем связанные данные
            'category' => $this->whenLoaded('category', function () {
                return new CategoryResource($this->category);
            }),
        ];
    }
}