<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantFrontendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Process images to match frontend format
        $imagesData = [];
        if ($this->whenLoaded('images') && $this->images && $this->images->isNotEmpty()) {
            $baseUrl = config('app.url') . '/storage';
            $imagesData = $this->images->map(function ($image) use ($baseUrl) {
                return [
                    'thumbnail' => $baseUrl . $image->path,
                    'large' => $baseUrl . $image->path,
                ];
            })->toArray();
        }

        return [
            'id' => (string) $this->id,
            'variantId' => $this->variant_id,
            'color' => $this->color?->name,
            'colorHex' => $this->whenLoaded('color', fn() => $this->color?->hex),
            'size' => $this->size?->name,
            'sizeId' => $this->whenLoaded('size', fn() => $this->size?->slug),
            'packSize' => $this->whenLoaded('packOption', fn() => $this->packOption?->value),
            'packSizeId' => $this->whenLoaded('packOption', fn() => $this->packOption?->value),
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'salePrice' => $this->sale_price ? (float) $this->sale_price : null,
            'inStock' => $this->stock_qty > 0,
            'stock' => (int) $this->stock_qty, // Keep for compatibility
            'images' => $imagesData,
            'attributes' => $this->attributes,
        ];
    }
}