<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackOption extends Model
{
    protected $fillable = [
        'value',
        'label',
        'slug',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->label;
    }
}