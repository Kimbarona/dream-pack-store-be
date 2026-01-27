<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;

class SettingsService
{
    private const CACHE_KEY = 'site_settings';
    private const CACHE_TTL = 3600; // 1 hour

    public function getSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = Setting::all()->pluck('value', 'key')->toArray();
            
            // Process settings
            $processed = [
                'site_logo' => $this->getLogoUrl($settings['site_logo'] ?? null),
                'slogan' => $settings['slogan'] ?? '',
                'banners' => $this->processBanners($settings['banners'] ?? []),
            ];

            return $processed;
        });
    }

    public function updateSettings(array $data): array
    {
        // Handle logo upload
        if (isset($data['site_logo']) && $data['site_logo'] instanceof UploadedFile) {
            $logoPath = $data['site_logo']->store('settings', 'public');
            $this->deleteOldLogo();
            Setting::set('site_logo', $logoPath);
        } elseif (isset($data['remove_logo']) && $data['remove_logo']) {
            $this->deleteOldLogo();
            Setting::set('site_logo', null);
        }

        // Handle slogan
        if (isset($data['slogan'])) {
            Setting::set('slogan', $data['slogan']);
        }

        // Handle banners
        if (isset($data['banners'])) {
            $this->updateBanners($data['banners']);
        }

        // Handle banner deletions
        if (isset($data['delete_banners'])) {
            $this->deleteBanners($data['delete_banners']);
        }

        // Invalidate cache
        $this->clearCache();

        return $this->getSettings();
    }

    private function updateBanners(array $bannersData): void
    {
        $currentBanners = Setting::get('banners', []);

        foreach ($bannersData as $index => $bannerData) {
            $bannerId = $bannerData['id'] ?? null;

            if ($bannerId && isset($currentBanners[$bannerId])) {
                // Update existing banner
                $banner = $currentBanners[$bannerId];

                if (isset($bannerData['image']) && $bannerData['image'] instanceof UploadedFile) {
                    // Delete old image
                    if (isset($banner['image'])) {
                        Storage::disk('public')->delete($banner['image']);
                    }
                    // Upload new image
                    $banner['image'] = $bannerData['image']->store('banners', 'public');
                }

                $banner['link'] = $bannerData['link'] ?? $banner['link'] ?? '';
                $banner['sort_order'] = $bannerData['sort_order'] ?? $banner['sort_order'] ?? 0;

                $currentBanners[$bannerId] = $banner;
            } elseif (isset($bannerData['image']) && $bannerData['image'] instanceof UploadedFile) {
                // Create new banner
                $newBanner = [
                    'id' => uniqid('banner_', true),
                    'image' => $bannerData['image']->store('banners', 'public'),
                    'link' => $bannerData['link'] ?? '',
                    'sort_order' => $bannerData['sort_order'] ?? 0,
                ];

                $currentBanners[$newBanner['id']] = $newBanner;
            }
        }

        // Sort banners by sort_order
        uasort($currentBanners, function ($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        Setting::set('banners', array_values($currentBanners));
    }

    public function deleteBanners(array $bannerIds): void
    {
        $currentBanners = Setting::get('banners', []);

        foreach ($bannerIds as $bannerId) {
            if (isset($currentBanners[$bannerId])) {
                // Delete image file
                if (isset($currentBanners[$bannerId]['image'])) {
                    Storage::disk('public')->delete($currentBanners[$bannerId]['image']);
                }
                // Remove from array
                unset($currentBanners[$bannerId]);
            }
        }

        Setting::set('banners', array_values($currentBanners));
    }

    private function deleteOldLogo(): void
    {
        $oldLogo = Setting::get('site_logo');
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }
    }

    private function getLogoUrl(?string $logoPath): ?string
    {
        if (!$logoPath) {
            return null;
        }

        return Storage::url($logoPath);
    }

    private function processBanners(array $banners): array
    {
        return array_map(function ($banner) {
            return [
                'id' => $banner['id'] ?? '',
                'image' => $this->getLogoUrl($banner['image'] ?? ''),
                'link' => $banner['link'] ?? '',
                'sort_order' => $banner['sort_order'] ?? 0,
            ];
        }, $banners);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function clearCacheStatic(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function getPublicSettings(): array
    {
        $settings = $this->getSettings();

        return [
            'site_logo' => $settings['site_logo'],
            'slogan' => $settings['slogan'],
            'banners' => array_map(function ($banner) {
                return [
                    'image' => $banner['image'],
                    'link' => $banner['link'],
                ];
            }, $settings['banners']),
        ];
    }
}