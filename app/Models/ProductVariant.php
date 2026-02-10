<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'pack_option_id',
        'sku',
        'price',
        'sale_price',
        'stock_qty',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_qty' => 'integer',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function packOption(): BelongsTo
    {
        return $this->belongsTo(PackOption::class, 'pack_option_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('sort_order');
    }

    public function getVariantIdAttribute(): string
    {
        $productSlug = $this->product ? $this->product->slug : 'unknown';
        $colorSlug = (array_key_exists('color', $this->relations) && $this->color) ? $this->color->slug : 'default';
        $sizeSlug = (array_key_exists('size', $this->relations) && $this->size) ? $this->size->slug : 'default';
        $packValue = (array_key_exists('packOption', $this->relations) && $this->packOption) ? $this->packOption->value : 1;
        
        $parts = [$productSlug, $colorSlug, $sizeSlug, $packValue];
        
        return implode('-', $parts);
    }

    public function getColorHexAttribute(): ?string
    {
        if (array_key_exists('color', $this->relations)) {
            return $this->color?->hex;
        }
        return null;
    }

    public function getSizeIdAttribute(): ?string
    {
        if (array_key_exists('size', $this->relations)) {
            return $this->size?->slug;
        }
        return null;
    }

    public function getPackSizeAttribute(): ?int
    {
        if (array_key_exists('packOption', $this->relations)) {
            return $this->packOption?->value;
        }
        return null;
    }

    public function getPackSizeIdAttribute(): ?int
    {
        if (array_key_exists('packOption', $this->relations)) {
            return $this->packOption?->value;
        }
        return null;
    }

    public function getDisplayPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    public function getDisplaySalePriceAttribute(): ?string
    {
        return $this->sale_price ? number_format($this->sale_price, 2) : null;
    }

    public function isInStock(): bool
    {
        return $this->stock_qty > 0;
    }

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock_qty > 0 && $this->stock_qty <= $threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_qty <= 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_qty', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_qty', '<=', 0);
    }
}