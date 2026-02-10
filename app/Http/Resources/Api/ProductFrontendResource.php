<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\VariantFrontendResource;

class ProductFrontendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Load variants with relationships
        $variants = $this->whenLoaded('variants', fn() => $this->variants->load(['color', 'size', 'packOption', 'images']));
        
        // Get category information
        $category = $this->whenLoaded('category') ? $this->category : 
                   ($this->whenLoaded('categories') && $this->categories->isNotEmpty() ? $this->categories->first() : null);
        
        $categoryName = $category?->name ?? 'Uncategorized';
        
        // Get options from variants
        $colors = [];
        $sizes = [];
        $pcsPerPack = [];
        
        if ($variants && $variants->isNotEmpty()) {
            // Get unique colors
            $uniqueColors = $variants->whereNotNull('color_id')->pluck('color')->unique('id');
            $colors = $uniqueColors->map(function ($color) {
                return [
                    'id' => $color->slug,
                    'name' => $color->name,
                    'value' => $color->slug,
                    'hex' => $color->hex,
                ];
            })->toArray();
            
            // Get unique sizes
            $uniqueSizes = $variants->whereNotNull('size_id')->pluck('size')->unique('id');
            $sizes = $uniqueSizes->map(function ($size) {
                return [
                    'id' => $size->slug,
                    'name' => $size->name,
                    'value' => $size->slug,
                ];
            })->toArray();
            
            // Get unique pack options
            $uniquePackOptions = $variants->whereNotNull('pack_option_id')->pluck('packOption')->unique('id');
            $pcsPerPack = $uniquePackOptions->map(function ($packOption) {
                return [
                    'id' => (string) $packOption->value,
                    'name' => $packOption->label,
                    'value' => $packOption->value,
                    'label' => $packOption->label,
                ];
            })->toArray();
        }
        
        // Fallback to product images if no variant images
        $productImages = [];
        if ($this->whenLoaded('images') && $this->images->isNotEmpty()) {
            $baseUrl = config('app.url') . '/storage';
            $productImages = $this->images->map(function ($image) use ($baseUrl) {
                return [
                    'thumbnail' => $baseUrl . $image->path,
                    'large' => $baseUrl . $image->path,
                ];
            })->toArray();
        }
        
        // Build variants data
        $variantsData = [];
        if ($variants && $variants->isNotEmpty()) {
            $variantsData = VariantFrontendResource::collection($variants)->toArray($request);
        } else {
            // Create default variant from product data
            $variantsData = [[
                'id' => (string) $this->id,
                'variantId' => $this->slug . '-default',
                'color' => null,
                'colorHex' => null,
                'size' => null,
                'sizeId' => null,
                'packSize' => (int) ($this->pieces_per_package ?? 1),
                'packSizeId' => (int) ($this->pieces_per_package ?? 1),
                'sku' => $this->sku,
                'price' => (float) $this->price,
                'salePrice' => $this->sale_price ? (float) $this->sale_price : null,
                'inStock' => $this->stock_qty > 0,
                'stock' => (int) $this->stock_qty,
                'images' => $productImages,
                'attributes' => null,
            ]];
        }

        return [
            'id' => $this->id,
            'name' => $this->title,
            'slug' => $this->slug,
            'baseDescription' => [
                'en' => $this->base_description['en'] ?? $this->description ?? '',
                'he' => $this->base_description['he'] ?? '',
            ],
            'category' => $category ? [
                'id' => $category->id,
                'categoryName' => $category->name,
                'description' => $category->description,
                'parent' => $category->parent?->name,
                'tag' => $category->tag,
            ] : null,
            'rating' => (float) ($this->rating ?? 0),
            'soldCount' => (int) ($this->sold_count ?? 0),
            'price' => $variants && $variants->isNotEmpty() ? (float) $variants->min('price') : (float) $this->price,
            'salePrice' => $variants && $variants->isNotEmpty() ? 
                ($variants->whereNotNull('sale_price')->isNotEmpty() ? (float) $variants->whereNotNull('sale_price')->min('sale_price') : null) : 
                ($this->sale_price ? (float) $this->sale_price : null),
            'options' => [
                'colors' => $colors,
                'sizes' => $sizes,
                'pcsPerPack' => $pcsPerPack,
            ],
            'variants' => $variantsData,
            'parent' => $category?->parent?->name ?? $categoryName,
            'tag' => $category?->tag ?? null,
            'createdAt' => $this->created_at?->toIso8601ZuluString(),
            'updatedAt' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}