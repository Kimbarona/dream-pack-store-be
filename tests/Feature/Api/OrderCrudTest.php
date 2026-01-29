<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
    }

    /** @test */
    public function it_can_create_order_with_valid_data()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 10]);
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'size' => 'M',
                    'color' => 'Blue'
                ]
            ],
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'payment_method' => 'crypto',
            'notes' => 'Test order notes'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'order_number',
                'status',
                'subtotal',
                'tax',
                'shipping',
                'total',
                'items',
                'user',
                'created_at'
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'payment_method' => 'crypto'
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size' => 'M',
            'color' => 'Blue'
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_create_order()
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_order_creation_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'items' => [],
            'payment_method' => 'invalid_method'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items', 'payment_method']);
    }

    /** @test */
    public function it_cannot_create_order_with_insufficient_stock()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 1]);
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5, // More than available stock
                ]
            ],
            'shipping_address_id' => $address->id,
            'payment_method' => 'crypto'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_can_view_own_order()
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'order_number',
                'status',
                'subtotal',
                'tax',
                'shipping',
                'total',
                'items',
                'user',
                'created_at'
            ])
            ->assertJson(['id' => $order->id]);
    }

    /** @test */
    public function it_cannot_view_other_users_order()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_list_own_orders()
    {
        Sanctum::actingAs($this->user);

        $orders = Order::factory()->count(3)->create(['user_id' => $this->user->id]);
        Order::factory()->count(2)->create(); // Other user's orders

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'status',
                        'total',
                        'created_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_to_view_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_calculates_order_totals_correctly()
    {
        Sanctum::actingAs($this->user);

        $product1 = Product::factory()->create(['price' => 100.00]);
        $product2 = Product::factory()->create(['price' => 50.00]);
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                ]
            ],
            'shipping_address_id' => $address->id,
            'payment_method' => 'crypto'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $order = Order::first();
        
        // Subtotal: (100 * 2) + (50 * 1) = 250
        $this->assertEquals(250.00, $order->subtotal);
        
        // Tax (8%): 250 * 0.08 = 20
        $this->assertEquals(20.00, $order->tax);
        
        // Shipping: 10 (default)
        $this->assertEquals(10.00, $order->shipping);
        
        // Total: 250 + 20 + 10 = 280
        $this->assertEquals(280.00, $order->total);
    }

    /** @test */
    public function it_handles_order_creation_with_discounts()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['price' => 100.00, 'discount' => 10.00]);
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]
            ],
            'shipping_address_id' => $address->id,
            'payment_method' => 'crypto'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $order = Order::first();
        $orderItem = $order->items->first();
        
        // Should use discounted price: 100 - 10 = 90
        $this->assertEquals(90.00, $orderItem->price);
        $this->assertEquals(90.00, $order->subtotal);
    }

    /** @test */
    public function it_creates_order_with_product_snapshots()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create([
            'title' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 100.00
        ]);
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]
            ],
            'shipping_address_id' => $address->id,
            'payment_method' => 'crypto'
        ];

        $this->postJson('/api/orders', $orderData);

        $orderItem = OrderItem::first();
        
        // Should preserve product data at time of order
        $this->assertEquals('Test Product', $orderItem->product_title);
        $this->assertEquals('TEST-001', $orderItem->product_sku);
        $this->assertEquals(100.00, $orderItem->product_price);
    }

    /** @test */
    public function it_updates_inventory_on_order_creation()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 10]);
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ]
            ],
            'shipping_address_id' => $address->id,
            'payment_method' => 'crypto'
        ];

        $this->postJson('/api/orders', $orderData);

        $product->refresh();
        $this->assertEquals(7, $product->stock); // 10 - 3 = 7
    }
}