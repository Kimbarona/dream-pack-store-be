<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'meta_title',
        'meta_description',
        'is_active',
        'tag',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    public function directProducts(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->name;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    public function getParentAttribute(): ?string
    {
        // Check if the relationship is loaded
        if (array_key_exists('parent', $this->relations) && $this->relations['parent'] !== null) {
            return $this->relations['parent']->name;
        }
        
        // If not loaded, try to access it safely
        try {
            $parent = $this->getRelationValue('parent');
            return $parent?->name;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTagAttribute(): ?string
    {
        return $this->attributes['tag'] ?? null;
    }
}