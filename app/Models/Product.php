<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'base_description',
        'price',
        'sale_price',
        'sku',
        'stock_qty',
        'minimum_stock',
        'track_inventory',
        'sort_order',
        'is_active',
        'is_featured',
        'meta_title',
        'pieces_per_package',
        'rating',
        'sold_count',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_inventory' => 'boolean',
        'base_description' => 'array',
        'meta_description' => 'array',
        'rating' => 'decimal:2',
        'sold_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort_order');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_qty', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
                    ->whereColumn('stock_qty', '<=', 'minimum_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('track_inventory', true)
                    ->where('stock_qty', '<=', 0);
    }

    public function isLowStock(): bool
    {
        return $this->track_inventory && $this->stock_qty <= $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->track_inventory && $this->stock_qty <= 0;
    }

    public function getSizeAttribute()
    {
        $sizeValue = $this->attributeValues()->whereHas('attribute', function ($query) {
            $query->where('slug', 'size');
        })->first();

        return $sizeValue ? $sizeValue->value : null;
    }

    public function getColorsAttribute()
    {
        return $this->attributeValues()->whereHas('attribute', function ($query) {
            $query->where('slug', 'color');
        })->get();
    }

    /**
     * Relationship for featured image (optimized for Filament tables)
     */
    public function featuredImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_featured', true);
    }

    /**
     * Get featured image URL
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        $featuredImage = $this->images()->where('is_featured', true)->first();
        if (!$featuredImage) {
            $featuredImage = $this->images()->first();
        }
        return $featuredImage ? $featuredImage->url : null;
    }

    /**
     * Get available sizes from dedicated sizes table or attribute values
     */
    public function getAvailableSizesAttribute(): array
    {
        // First try to get from dedicated sizes table
        $sizes = $this->sizes->pluck('value')->toArray();
        
        // If no dedicated sizes, fall back to attribute values
        if (empty($sizes)) {
            $sizeValue = $this->attributeValues()->whereHas('attribute', function ($query) {
                $query->where('slug', 'size');
            })->first();
            
            if ($sizeValue) {
                $sizes = [$sizeValue->value];
            }
        }
        
        return $sizes;
    }

    /**
     * Get available colors from dedicated colors table or attribute values
     */
    public function getAvailableColorsAttribute(): array
    {
        // First try to get from dedicated colors table
        $colors = $this->colors->map(function ($color) {
            return [
                'id' => $color->id,
                'name' => $color->name,
                'hex' => $color->hex,
                'image_url' => $color->image_url,
            ];
        })->toArray();
        
        // If no dedicated colors, fall back to attribute values
        if (empty($colors)) {
            $colors = $this->colors->map(function ($color) {
                return [
                    'id' => $color->id,
                    'name' => $color->value,
                    'hex' => null,
                    'image_url' => null,
                ];
            })->toArray();
        }
        
        return $colors;
    }

    /**
     * Get minimum price from variants
     */
    public function getMinPriceAttribute(): float
    {
        if ($this->variants->isEmpty()) {
            return (float) $this->price;
        }
        
        return $this->variants->min('price');
    }

    /**
     * Get minimum sale price from variants
     */
    public function getMinSalePriceAttribute(): ?float
    {
        if ($this->variants->isEmpty()) {
            return $this->sale_price ? (float) $this->sale_price : null;
        }
        
        $salePrices = $this->variants->whereNotNull('sale_price')->pluck('sale_price');
        return $salePrices->isNotEmpty() ? $salePrices->min() : null;
    }

    /**
     * Get colors option for frontend API
     */
    public function getColorsOptionAttribute(): array
    {
        $colors = $this->variants->whereNotNull('color_id')->pluck('color')->unique('id');
        
        return $colors->map(function ($color) {
            return [
                'id' => $color->slug,
                'name' => $color->name,
                'value' => $color->slug,
                'hex' => $color->hex,
            ];
        })->toArray();
    }

    /**
     * Get sizes option for frontend API
     */
    public function getSizesOptionAttribute(): array
    {
        $sizes = $this->variants->whereNotNull('size_id')->pluck('size')->unique('id');
        
        return $sizes->map(function ($size) {
            return [
                'id' => $size->slug,
                'name' => $size->name,
                'value' => $size->slug,
            ];
        })->toArray();
    }

    /**
     * Get pcsPerPack option for frontend API
     */
    public function getPcsPerPackOptionAttribute(): array
    {
        $packOptions = $this->variants->whereNotNull('pack_option_id')->pluck('packOption')->unique('id');
        
        return $packOptions->map(function ($packOption) {
            return [
                'id' => (string) $packOption->value,
                'name' => $packOption->label,
                'value' => $packOption->value,
                'label' => $packOption->label,
            ];
        })->toArray();
    }

    /**
     * Get variants for frontend API
     */
    public function getVariantsForFrontendAttribute(): array
    {
        return $this->variants->map(function ($variant) {
            return [
                'variantId' => $variant->variant_id,
                'color' => $variant->color?->name,
                'colorHex' => $variant->color_hex,
                'size' => $variant->size?->name,
                'sizeId' => $variant->size_id,
                'packSize' => $variant->pack_size,
                'packSizeId' => $variant->pack_size_id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'salePrice' => $variant->sale_price ? (float) $variant->sale_price : null,
                'stock' => $variant->stock_qty,
                'images' => $variant->images->map(function ($image) {
                    return $image->url;
                })->toArray(),
                'attributes' => $variant->attributes,
            ];
        })->toArray();
    }

    /**
     * Check if product has variants
     */
    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    /**
     * Get total stock across all variants
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->variants->isEmpty()) {
            return $this->stock_qty;
        }
        
        return $this->variants->sum('stock_qty');
    }
}