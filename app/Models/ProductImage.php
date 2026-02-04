<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'disk',
        'alt_text',
        'sort_order',
        'is_featured',
    ];

    protected $attributes = [
        'disk' => 'public',
        'sort_order' => 0,
        'is_featured' => false,
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($image) {
            if (!$image->product_id) {
                throw new \Exception('Product ID is required for images');
            }
        });
    }

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}