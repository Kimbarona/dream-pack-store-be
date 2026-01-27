<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'parent_id' => $this->parent_id,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products_count' => $this->when(
                $this->products_count ?? $this->whenLoaded('products', fn() => $this->products->count()),
                $this->products_count ?? $this->whenLoaded('products', fn() => $this->products->count())
            ),
        ];
    }
}