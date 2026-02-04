<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? $this->getKey(),
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'stock_qty' => $this->stock_qty,
            'is_active' => $this->is_active,
            
            // Lightweight fields
            'featured_image' => $this->whenLoaded('images', function() {
                return optional($this->images->first())->url;
            }),
            'categories' => $this->whenLoaded('categories', function() {
                return $this->categories->pluck('name')->toArray();
            }),
            
            // Computed fields
            'in_stock' => $this->stock_qty > 0 || !$this->track_inventory,
            'on_sale' => !is_null($this->sale_price),
            'discount_percentage' => $this->sale_price ? 
                round((($this->price - $this->sale_price) / $this->price) * 100, 0) : null,
        ];
    }
}