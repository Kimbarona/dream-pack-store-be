<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BannerImage extends Model
{
    protected $fillable = [
        'banner_id',
        'path',
        'disk',
        'sort_order',
        'is_mobile',
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
