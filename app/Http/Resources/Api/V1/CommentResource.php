<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'author' => $this->author,
            'post_id' => $this->post_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Добавляем связанные данные поста
            'post' => $this->whenLoaded('post', function () {
                return [
                    'id' => $this->post->id,
                    'title' => $this->post->title,
                ];
            }),
        ];
    }
    
    public function toResponse($request)
    {
        return parent::toResponse($request)->setEncodingOptions(JSON_PRETTY_PRINT);
    }
}