<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductFrontendCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => ProductFrontendResource::collection($this->collection),
                'pagination' => [
                    'current_page' => $this->resource->currentPage(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'total' => $this->resource->total(),
                    'has_more' => $this->resource->hasMorePages(),
                ]
            ]
        ];
    }

    public function with($request)
    {
        return [];
    }
}