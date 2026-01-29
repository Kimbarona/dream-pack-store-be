<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'price',
        'sale_price',
        'sku',
        'stock_qty',
        'track_inventory',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
        'pcs_per_pack',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'track_inventory' => 'boolean',
    ];

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
}