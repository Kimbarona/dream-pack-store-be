<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'title',
        'description',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'string',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope a query to get settings by group
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope a query to get only public settings
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Get setting value with caching
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember('site_setting_' . $key, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'number' => is_numeric($setting->value) ? $setting->value + 0 : $default,
                'json' => json_decode($setting->value, true) ?: $default,
                'file' => $setting->value ? Storage::url($setting->value) : $default,
                default => $setting->value,
            };
        });
    }

    /**
     * Set setting value and clear cache
     */
    public static function setValue(string $key, mixed $value, string $type = 'text', string $group = 'general'): void
    {
        $processedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $processedValue, 
                'type' => $type, 
                'group' => $group,
                'title' => ucwords(str_replace('_', ' ', $key)),
            ]
        );

        Cache::forget('site_setting_' . $key);
    }

    /**
     * Get all settings by group with caching
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember('site_settings_group_' . $group, 3600, function () use ($group) {
            return static::byGroup($group)
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(function ($setting) {
                    $value = match ($setting->type) {
                        'boolean' => (bool) $setting->value,
                        'number' => is_numeric($setting->value) ? $setting->value + 0 : $setting->value,
                        'json' => json_decode($setting->value, true) ?: $setting->value,
                        'file' => $setting->value ? Storage::url($setting->value) : $setting->value,
                        default => $setting->value,
                    };
                    return [$setting->key => $value];
                })
                ->toArray();
        });
    }

    /**
     * Clear all site settings cache
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
