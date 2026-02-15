<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Category;
use Tests\TestCase;

class ProductFeaturedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed categories first
        $this->seed(CategorySeeder::class);
    }

    public function test_featured_products_endpoint_returns_success()
    {
        // Create test products
        $featuredProduct = Product::factory()->create([
            'is_featured' => true,
            'is_active' => true,
        ]);

        $nonFeaturedProduct = Product::factory()->create([
            'is_featured' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products/featured');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Featured products retrieved successfully',
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'products' => [
                            '*' => [
                                'id',
                                'title',
                                'slug',
                                'description',
                                'price',
                                'salePrice',
                                'sku',
                                'stock',
                                'rating',
                                'soldCount',
                                'category',
                                'images',
                                'variants',
                                'options',
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total',
                            'has_more',
                        ]
                    ]
                ]);
    }

    public function test_featured_products_only_returns_featured_products()
    {
        // Create test products
        $featuredProducts = Product::factory()->count(3)->create([
            'is_featured' => true,
            'is_active' => true,
        ]);

        $nonFeaturedProducts = Product::factory()->count(5)->create([
            'is_featured' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products/featured');

        $response->assertStatus(200);
        
        $data = $response->json();
        $productIds = collect($data['data']['products'])->pluck('id');
        
        // Assert only featured products are returned
        foreach ($featuredProducts as $product) {
            $this->assertContains($product->id, $productIds);
        }
        
        foreach ($nonFeaturedProducts as $product) {
            $this->assertNotContains($product->id, $productIds);
        }
    }

    public function test_featured_products_respects_limit_parameter()
    {
        // Create more featured products than the limit
        Product::factory()->count(5)->create([
            'is_featured' => true,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products/featured?limit=2');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(2, $data['data']['products']);
        $this->assertEquals(2, $data['data']['pagination']['per_page']);
    }

    public function test_featured_products_respects_is_active_parameter()
    {
        // Create active featured product
        $activeFeaturedProduct = Product::factory()->create([
            'is_featured' => true,
            'is_active' => true,
        ]);

        // Create inactive featured product
        $inactiveFeaturedProduct = Product::factory()->create([
            'is_featured' => true,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/products/featured?is_active=1');

        $response->assertStatus(200);
        
        $data = $response->json();
        $productIds = collect($data['data']['products'])->pluck('id');
        
        $this->assertContains($activeFeaturedProduct->id, $productIds);
        $this->assertNotContains($inactiveFeaturedProduct->id, $productIds);
    }

    public function test_featured_products_with_no_featured_products()
    {
        // Create only non-featured products
        Product::factory()->count(3)->create([
            'is_featured' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products/featured');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Featured products retrieved successfully',
                ]);
        
        $data = $response->json();
        $this->assertCount(0, $data['data']['products']);
        $this->assertEquals(0, $data['data']['pagination']['total']);
    }
}
