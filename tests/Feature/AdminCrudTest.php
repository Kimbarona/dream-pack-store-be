<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Banner;
use App\Models\BannerImage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create super admin user
        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        
        $this->admin->assignRole('super_admin');
        $this->actingAs($this->admin, 'admin');
    }

    // ========== BANNER CRUD TESTS ==========
    
    /** @test */
    public function it_can_list_banners()
    {
        $banners = Banner::factory()->count(3)->create();
        
        $response = $this->get('/admin/banners');
        
        $response->assertStatus(200);
        $response->assertSee($banners[0]->name);
        $response->assertSee($banners[1]->name);
    }

    /** @test */
    public function it_can_create_banner()
    {
        $bannerData = [
            'name' => 'Test Banner',
            'title' => 'Test Title',
            'subtitle' => 'Test Subtitle',
            'link_url' => 'https://example.com',
            'is_active' => true,
            'sort_order' => 1,
        ];

        $response = $this->post('/admin/banners', $bannerData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('banners', ['name' => 'Test Banner']);
    }

    /** @test */
    public function it_can_upload_banner_gallery_images()
    {
        Storage::fake('public');
        
        $banner = Banner::factory()->create();
        $images = [
            UploadedFile::fake()->image('banner1.jpg'),
            UploadedFile::fake()->image('banner2.jpg'),
        ];

        $response = $this->post("/admin/banners/{$banner->id}/relate-images", [
            'gallery' => $images,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(2, BannerImage::where('banner_id', $banner->id)->count());
        
        // Check files exist in storage
        $bannerImages = BannerImage::where('banner_id', $banner->id)->get();
        foreach ($bannerImages as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    /** @test */
    public function it_can_edit_banner()
    {
        $banner = Banner::factory()->create(['name' => 'Original Name']);

        $response = $this->put("/admin/banners/{$banner->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('banners', ['id' => $banner->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_banner()
    {
        $banner = Banner::factory()->create();

        $response = $this->delete("/admin/banners/{$banner->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    // ========== PRODUCT CRUD TESTS ==========

    /** @test */
    public function it_can_list_products()
    {
        $products = Product::factory()->count(3)->create();
        
        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
        $response->assertSee($products[0]->title);
        $response->assertSee($products[1]->title);
    }

    /** @test */
    public function it_can_create_product()
    {
        $productData = [
            'title' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test Description',
            'price' => 99.99,
            'sku' => 'TEST-SKU',
            'stock_qty' => 10,
            'track_inventory' => true,
            'sort_order' => 1,
            'is_active' => true,
            'pcs_per_pack' => 1,
        ];

        $response = $this->post('/admin/products', $productData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('products', ['title' => 'Test Product', 'sku' => 'TEST-SKU']);
    }

    /** @test */
    public function it_can_edit_product()
    {
        $product = Product::factory()->create(['title' => 'Original Title']);

        $response = $this->put("/admin/products/{$product->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function it_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->delete("/admin/products/{$product->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // ========== CATEGORY CRUD TESTS ==========

    /** @test */
    public function it_can_list_categories()
    {
        $categories = Category::factory()->count(3)->create();
        
        $response = $this->get('/admin/categories');
        
        $response->assertStatus(200);
        $response->assertSee($categories[0]->name);
        $response->assertSee($categories[1]->name);
    }

    /** @test */
    public function it_can_create_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description',
            'sort_order' => 1,
            'is_active' => true,
        ];

        $response = $this->post('/admin/categories', $categoryData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('categories', ['name' => 'Test Category', 'slug' => 'test-category']);
    }

    /** @test */
    public function it_can_edit_category()
    {
        $category = Category::factory()->create(['name' => 'Original Name']);

        $response = $this->put("/admin/categories/{$category->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->delete("/admin/categories/{$category->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // ========== ORDER CRUD TESTS ==========

    /** @test */
    public function it_can_list_orders()
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);
        
        $response = $this->get('/admin/orders');
        
        $response->assertStatus(200);
        $response->assertSee($orders[0]->order_number);
        $response->assertSee($orders[1]->order_number);
    }

    /** @test */
    public function it_can_view_order()
    {
        $order = Order::factory()->create();

        $response = $this->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    /** @test */
    public function it_can_edit_order()
    {
        $order = Order::factory()->create(['status' => 'pending_payment']);

        $response = $this->put("/admin/orders/{$order->id}", [
            'status' => 'paid_confirmed',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid_confirmed']);
    }

    /** @test */
    public function it_can_delete_order()
    {
        $order = Order::factory()->create();

        $response = $this->delete("/admin/orders/{$order->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    // ========== ADMIN CRUD TESTS ==========

    /** @test */
    public function it_can_list_admins()
    {
        $admins = Admin::factory()->count(3)->create();
        
        $response = $this->get('/admin/admins');
        
        $response->assertStatus(200);
        $response->assertSee($admins[0]->name);
        $response->assertSee($admins[1]->name);
    }

    /** @test */
    public function it_can_create_admin()
    {
        $adminData = [
            'name' => 'Test Admin',
            'email' => 'testadmin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => true,
        ];

        $response = $this->post('/admin/admins', $adminData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('admins', ['name' => 'Test Admin', 'email' => 'testadmin@example.com']);
    }

    /** @test */
    public function it_can_edit_admin()
    {
        $admin = Admin::factory()->create(['name' => 'Original Name']);

        $response = $this->put("/admin/admins/{$admin->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_admin()
    {
        $admin = Admin::factory()->create();

        $response = $this->delete("/admin/admins/{$admin->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }
}