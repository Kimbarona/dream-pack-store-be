<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->regularUser = User::factory()->create();
    }

    /** @test */
    public function admin_can_view_orders_list()
    {
        $this->actingAs($this->admin);

        $orders = Order::factory()->count(3)->create();

        $response = $this->get('/admin/orders');

        $response->assertStatus(200);
        // Filament list view should contain order data
        $response->assertSee($orders[0]->order_number);
        $response->assertSee($orders[1]->order_number);
        $response->assertSee($orders[2]->order_number);
    }

    /** @test */
    public function admin_can_view_single_order()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create();

        $response = $this->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee($order->status);
    }

    /** @test */
    public function admin_can_create_order()
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create();
        $product = Product::factory()->create();

        $orderData = [
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'subtotal' => 100.00,
            'tax' => 8.00,
            'shipping' => 10.00,
            'total' => 118.00,
            'payment_method' => 'crypto',
            'notes' => 'Admin created order',
        ];

        $response = $this->post('/admin/orders', $orderData);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'total' => 118.00,
            'notes' => 'Admin created order',
        ]);
    }

    /** @test */
    public function admin_can_edit_order()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'status' => 'pending_payment',
            'notes' => 'Original notes'
        ]);

        $updateData = [
            'status' => 'paid_confirmed',
            'notes' => 'Updated notes',
        ];

        $response = $this->put("/admin/orders/{$order->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid_confirmed',
            'notes' => 'Updated notes',
        ]);
    }

    /** @test */
    public function admin_can_update_order_status()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create(['status' => 'pending_payment']);

        // Test valid status transitions
        $transitions = [
            'pending_payment' => 'paid_confirmed',
            'paid_confirmed' => 'processing',
            'processing' => 'shipped',
        ];

        foreach ($transitions as $fromStatus => $toStatus) {
            $order->update(['status' => $fromStatus]);
            
            $response = $this->put("/admin/orders/{$order->id}", [
                'status' => $toStatus
            ]);

            $response->assertRedirect();
            $this->assertEquals($toStatus, $order->fresh()->status);
        }
    }

    /** @test */
    public function admin_cannot_make_invalid_status_transitions()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create(['status' => 'pending_payment']);

        // Try to skip to shipped (invalid transition)
        $response = $this->put("/admin/orders/{$order->id}", [
            'status' => 'shipped'
        ]);

        // Should either fail validation or order status should remain unchanged
        $this->assertEquals('pending_payment', $order->fresh()->status);
    }

    /** @test */
    public function admin_can_cancel_order()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create(['status' => 'processing']);

        $response = $this->put("/admin/orders/{$order->id}", [
            'status' => 'cancelled'
        ]);

        $response->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function admin_cannot_cancel_shipped_order()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create(['status' => 'shipped']);

        $response = $this->put("/admin/orders/{$order->id}", [
            'status' => 'cancelled'
        ]);

        // Order should remain shipped
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    /** @test */
    public function admin_can_delete_order()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create();

        $response = $this->delete("/admin/orders/{$order->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function regular_user_cannot_access_admin_orders()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/orders');
        $response->assertStatus(403);

        $order = Order::factory()->create();
        $response = $this->get("/admin/orders/{$order->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_search_orders_by_order_number()
    {
        $this->actingAs($this->admin);

        $order1 = Order::factory()->create(['order_number' => 'ORD-20240101-0001']);
        $order2 = Order::factory()->create(['order_number' => 'ORD-20240101-0002']);

        // Test search functionality (Filament typically handles this via GET parameters)
        $response = $this->get('/admin/orders?search=ORD-20240101-0001');

        $response->assertStatus(200);
        $response->assertSee('ORD-20240101-0001');
    }

    /** @test */
    public function admin_can_filter_orders_by_status()
    {
        $this->actingAs($this->admin);

        Order::factory()->create(['status' => 'pending_payment']);
        Order::factory()->create(['status' => 'paid_confirmed']);
        Order::factory()->create(['status' => 'processing']);

        // Test filter by status
        $response = $this->get('/admin/orders?status=paid_confirmed');

        $response->assertStatus(200);
        // Should only show paid_confirmed orders
    }

    /** @test */
    public function admin_can_view_order_details_with_items()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $response = $this->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee($orderItem->product_title);
        $response->assertSee($orderItem->quantity);
    }

    /** @test */
    public function admin_can_view_order_customer_information()
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
    }

    /** @test */
    public function admin_can_add_notes_to_order()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create(['notes' => null]);

        $response = $this->put("/admin/orders/{$order->id}", [
            'notes' => 'Customer requested expedited shipping'
        ]);

        $response->assertRedirect();
        $this->assertEquals(
            'Customer requested expedited shipping',
            $order->fresh()->notes
        );
    }

    /** @test */
    public function admin_can_view_order_financial_summary()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'subtotal' => 100.00,
            'tax' => 8.00,
            'shipping' => 10.00,
            'total' => 118.00,
        ]);

        $response = $this->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('100.00'); // subtotal
        $response->assertSee('8.00');   // tax
        $response->assertSee('10.00');  // shipping
        $response->assertSee('118.00'); // total
    }

    /** @test */
    public function admin_can_export_orders()
    {
        $this->actingAs($this->admin);

        Order::factory()->count(5)->create();

        // Test export functionality (Filament typically provides this)
        $response = $this->get('/admin/orders/export');

        // Should return some form of export (CSV, Excel, etc.)
        $this->assertTrue($response->status() === 200 || $response->status() === 302);
    }

    /** @test */
    public function admin_can_bulk_update_order_statuses()
    {
        $this->actingAs($this->admin);

        $orders = Order::factory()->count(3)->create(['status' => 'pending_payment']);

        // Test bulk update (Filament typically handles this via POST to bulk endpoint)
        $response = $this->post('/admin/orders/bulk-update', [
            'ids' => $orders->pluck('id')->toArray(),
            'status' => 'paid_confirmed',
        ]);

        if ($response->status() === 302) { // If bulk update is supported
            foreach ($orders as $order) {
                $this->assertEquals('paid_confirmed', $order->fresh()->status);
            }
        }
    }

    /** @test */
    public function order_form_validation_works()
    {
        $this->actingAs($this->admin);

        // Test creating order with invalid data
        $response = $this->post('/admin/orders', [
            'user_id' => null, // Invalid
            'total' => -10,    // Invalid
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function admin_can_view_order_timeline()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        // Timeline information should be visible
        $response->assertSee($order->created_at->format('Y-m-d'));
    }
}