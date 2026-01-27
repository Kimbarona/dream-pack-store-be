<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'hex',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Validate hex color format
     */
    public static function isValidHex(string $hex): bool
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $hex);
    }
}