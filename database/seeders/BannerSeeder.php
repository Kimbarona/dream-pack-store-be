<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;
use App\Models\BannerImage;
use Illuminate\Support\Facades\Storage;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Sample banner data
        $banners = [
            [
                'title' => 'Summer Sale Event',
                'subtitle' => 'Get up to 50% off on selected items',
                'link_url' => '/sale',
                'is_active' => true,
                'sort_order' => 1,
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(14),
                'image_count' => 3,
            ],
            [
                'title' => 'New Collection',
                'subtitle' => 'Discover our latest arrivals',
                'link_url' => '/new-collection',
                'is_active' => true,
                'sort_order' => 2,
                'starts_at' => now(),
                'ends_at' => null,
                'image_count' => 2,
            ],
            [
                'title' => 'Limited Time Offer',
                'subtitle' => 'Special discounts this week only',
                'link_url' => '/offers',
                'is_active' => true,
                'sort_order' => 3,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(5),
                'image_count' => 4,
            ],
            [
                'title' => 'Premium Quality',
                'subtitle' => 'Experience the difference',
                'link_url' => '/premium',
                'is_active' => true,
                'sort_order' => 4,
                'starts_at' => null,
                'ends_at' => null,
                'image_count' => 2,
            ],
            [
                'title' => 'Flash Sale',
                'subtitle' => '24 hours only - Don\'t miss out!',
                'link_url' => '/flash-sale',
                'is_active' => false,
                'sort_order' => 5,
                'starts_at' => now()->addDays(1),
                'ends_at' => now()->addDays(2),
                'image_count' => 3,
            ],
            [
                'title' => 'Weekend Special',
                'subtitle' => 'Extra savings this weekend',
                'link_url' => '/weekend',
                'is_active' => true,
                'sort_order' => 6,
                'starts_at' => now()->addDays(3),
                'ends_at' => now()->addDays(5),
                'image_count' => 2,
            ],
            [
                'title' => 'Member Exclusive',
                'subtitle' => 'Special deals for members',
                'link_url' => '/member-deals',
                'is_active' => true,
                'sort_order' => 7,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(7),
                'image_count' => 3,
            ],
            [
                'title' => 'Clearance Sale',
                'subtitle' => 'Final reductions - While stocks last',
                'link_url' => '/clearance',
                'is_active' => true,
                'sort_order' => 8,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(10),
                'image_count' => 4,
            ],
            [
                'title' => 'Holiday Collection',
                'subtitle' => 'Perfect gifts for every occasion',
                'link_url' => '/holiday',
                'is_active' => false,
                'sort_order' => 9,
                'starts_at' => now()->addDays(14),
                'ends_at' => now()->addDays(30),
                'image_count' => 3,
            ],
            [
                'title' => 'Best Sellers',
                'subtitle' => 'Our most popular products',
                'link_url' => '/best-sellers',
                'is_active' => true,
                'sort_order' => 10,
                'starts_at' => null,
                'ends_at' => null,
                'image_count' => 2,
            ],
            [
                'title' => 'Tech Tuesday',
                'subtitle' => 'Special deals on electronics',
                'link_url' => '/tech-tuesday',
                'is_active' => true,
                'sort_order' => 11,
                'starts_at' => now()->addDays(2),
                'ends_at' => now()->addDays(3),
                'image_count' => 3,
            ],
            [
                'title' => 'Fashion Forward',
                'subtitle' => 'Latest trends in fashion',
                'link_url' => '/fashion',
                'is_active' => true,
                'sort_order' => 12,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(14),
                'image_count' => 4,
            ],
        ];

        // Get sample banner images
        $sampleImages = glob(database_path('seeders/assets/banner_*.jpg'));

        foreach ($banners as $bannerData) {
            // Create banner
            $banner = Banner::create([
                'name' => $bannerData['title'], // Use title as name for now
                'title' => $bannerData['title'],
                'subtitle' => $bannerData['subtitle'],
                'link_url' => $bannerData['link_url'],
                'is_active' => $bannerData['is_active'],
                'sort_order' => $bannerData['sort_order'],
                'starts_at' => $bannerData['starts_at'],
                'ends_at' => $bannerData['ends_at'],
            ]);

            // Create banner images with file copying
            $this->createBannerImages($banner, $bannerData['image_count'], $sampleImages);

            $this->command->info("✓ Created banner: {$banner->title} with {$bannerData['image_count']} images");
        }

        $this->command->info('✅ Banners seeded successfully!');
    }

    private function createBannerImages(Banner $banner, int $imageCount, array $sampleImages): void
    {
        // Create banner directory in storage
        $bannerDir = "banners/{$banner->id}/gallery";
        Storage::disk('public')->makeDirectory($bannerDir);

        $firstImagePath = null;

        for ($i = 0; $i < $imageCount; $i++) {
            // Select a random sample image
            $sourceImage = $sampleImages[array_rand($sampleImages)];
            $sourceFilename = basename($sourceImage);
            
            // Generate unique filename
            $uniqueFilename = uniqid() . '_' . $sourceFilename;
            $destinationPath = $bannerDir . '/' . $uniqueFilename;
            
            // Copy file to storage
            $sourceContent = file_get_contents($sourceImage);
            Storage::disk('public')->put($destinationPath, $sourceContent);
            
            // Determine if this should be a mobile image (20% chance)
            $isMobile = (fake()->boolean(20) && $i > 0); // Don't make first image mobile
            
            // Create database record
            BannerImage::create([
                'banner_id' => $banner->id,
                'path' => $destinationPath,
                'disk' => 'public',
                'sort_order' => $i,
                'is_mobile' => $isMobile,
            ]);

            // Store first image path for banner main image
            if ($i === 0 && !$isMobile) {
                $firstImagePath = $destinationPath;
            }
        }

        // Note: Banners table doesn't have image_path column anymore
        // Images are stored separately in banner_images table
    }
}