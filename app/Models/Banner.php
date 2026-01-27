<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'image_mobile_path',
        'link_url',
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
    ];

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
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        return asset($this->image_path);
    }

    /**
     * Get the mobile image URL
     */
    public function getImageMobileUrlAttribute()
    {
        return $this->image_mobile_path ? asset($this->image_mobile_path) : $this->image_url;
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