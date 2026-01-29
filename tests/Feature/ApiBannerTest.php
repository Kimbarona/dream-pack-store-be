<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Banner;
use App\Models\BannerImage;

class ApiBannerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_active_banners_with_gallery_images()
    {
        // Create banner with multiple images
        $banner = Banner::factory()->create([
            'name' => 'Test Banner',
            'title' => 'Test Title',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        BannerImage::factory()->count(3)->create([
            'banner_id' => $banner->id,
            'is_mobile' => false,
        ]);

        $response = $this->getJson('/api/banners');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        
        $data = $response->json();
        $this->assertEquals($banner->id, $data[0]['id']);
        $this->assertEquals($banner->name, $data[0]['name']);
        $this->assertEquals($banner->title, $data[0]['title']);
        $this->assertIsArray($data[0]['images']);
        $this->assertCount(3, $data[0]['images']);
        
        // Check image structure
        $firstImage = $data[0]['images'][0];
        $this->assertArrayHasKey('url', $firstImage);
        $this->assertArrayHasKey('is_mobile', $firstImage);
        $this->assertArrayHasKey('sort_order', $firstImage);
    }

    /** @test */
    public function it_filters_banners_by_active_status_and_schedule()
    {
        // Create active banner
        $activeBanner = Banner::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        // Create inactive banner
        $inactiveBanner = Banner::factory()->create([
            'is_active' => false,
        ]);

        // Create scheduled future banner
        $futureBanner = Banner::factory()->create([
            'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/banners');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        
        $data = $response->json();
        $this->assertEquals($activeBanner->id, $data[0]['id']);
        $this->assertEquals($activeBanner->name, $data[0]['name']);
    }

    /** @test */
    public function it_respects_limit_parameter()
    {
        // Create more banners than limit
        Banner::factory()->count(10)->create(['is_active' => true]);

        $response = $this->getJson('/api/banners?limit=5');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    /** @test */
    public function it_returns_banners_in_sort_order()
    {
        // Create banners with different sort orders
        $banner1 = Banner::factory()->create(['sort_order' => 3, 'is_active' => true]);
        $banner2 = Banner::factory()->create(['sort_order' => 1, 'is_active' => true]);
        $banner3 = Banner::factory()->create(['sort_order' => 2, 'is_active' => true]);

        $response = $this->getJson('/api/banners');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        
        $data = $response->json();
        $this->assertEquals($banner2->id, $data[0]['id']); // sort_order 1
        $this->assertEquals($banner3->id, $data[1]['id']); // sort_order 2
        $this->assertEquals($banner1->id, $data[2]['id']); // sort_order 3
    }
}