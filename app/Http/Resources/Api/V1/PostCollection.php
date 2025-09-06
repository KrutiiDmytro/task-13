<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'version' => 'v1',
            ],
        ];
    }
    
    public function toResponse($request)
    {
        return parent::toResponse($request)->setEncodingOptions(JSON_PRETTY_PRINT);
    }
}