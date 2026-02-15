<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'subtitle',
        'link_url',
        'image',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'image' => 'string',
    ];

    protected function imageUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(function ($value) {
            $image = $this->image;
            if (!$image) {
                return null;
            }
            // If it's already a valid URL, return it directly
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            // Otherwise, it's a local file path
            return \Illuminate\Support\Facades\Storage::url($image);
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(BannerImage::class)->orderBy('sort_order');
    }

    public function desktopImages(): HasMany
    {
        return $this->images()->where('is_mobile', false);
    }

    public function mobileImages(): HasMany
    {
        return $this->images()->where('is_mobile', true);
    }

    /**
     * Scope to get only active banners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get banners within schedule
     */
    public function scopeScheduled($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')
              ->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')
              ->orWhere('ends_at', '>=', now());
        });
    }

    /**
     * Scope to get banners that should be displayed
     */
    public function scopeDisplayed($query)
    {
        return $query->active()->scheduled()->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Check if banner is currently active and within schedule
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }
        
        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }
        
        return true;
    }
}