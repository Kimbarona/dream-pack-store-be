<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'stock_qty' => $this->stock_qty,
            'track_inventory' => $this->track_inventory,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'pieces_per_package' => $this->pieces_per_package,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Attributes
            'size' => $this->size,
            'available_colors' => $this->colors->map(fn($color) => [
                'id' => $color->id,
                'value' => $color->value,
                'slug' => $color->slug,
            ]),
            
            // Images
            'images' => $this->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::url($image->path),
                'alt_text' => $image->alt_text,
                'sort_order' => $image->sort_order,
            ]),
            
            // Categories
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            
            // Additional computed fields
            'in_stock' => $this->stock_qty > 0 || !$this->track_inventory,
            'on_sale' => !is_null($this->sale_price),
            'discount_percentage' => $this->sale_price ? round((($this->price - $this->sale_price) / $this->price) * 100, 0) : null,
        ];
    }
}