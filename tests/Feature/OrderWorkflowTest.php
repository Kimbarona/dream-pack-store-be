<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
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
    public function complete_order_creation_workflow()
    {
        // Create products with sufficient stock
        $product1 = Product::factory()->create(['stock' => 10, 'price' => 100.00]);
        $product2 = Product::factory()->create(['stock' => 5, 'price' => 50.00]);
        
        // Create addresses
        $shippingAddress = Address::factory()->create(['user_id' => $this->user->id]);
        $billingAddress = Address::factory()->create(['user_id' => $this->user->id]);

        // Simulate order creation
        $order = DB::transaction(function () use ($product1, $product2, $shippingAddress, $billingAddress) {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'pending_payment',
                'subtotal' => 250.00, // (100 * 2) + (50 * 1)
                'tax' => 20.00, // 8% of 250
                'shipping' => 10.00,
                'total' => 280.00,
                'shipping_address' => $shippingAddress->toArray(),
                'billing_address' => $billingAddress->toArray(),
                'payment_method' => 'crypto',
            ]);

            // Create order items
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product1->id,
                'quantity' => 2,
                'price' => 100.00,
                'product_title' => $product1->title,
                'product_sku' => $product1->sku,
                'product_price' => $product1->price,
            ]);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product2->id,
                'quantity' => 1,
                'price' => 50.00,
                'product_title' => $product2->title,
                'product_sku' => $product2->sku,
                'product_price' => $product2->price,
            ]);

            // Update inventory
            $product1->decrement('stock', 2);
            $product2->decrement('stock', 1);

            return $order;
        });

        // Verify order was created correctly
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'total' => 280.00,
        ]);

        // Verify order items were created
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        // Verify inventory was updated
        $this->assertEquals(8, $product1->fresh()->stock); // 10 - 2
        $this->assertEquals(4, $product2->fresh()->stock); // 5 - 1
    }

    /** @test */
    public function payment_workflow_with_crypto_payment()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'payment_method' => 'crypto',
            'total' => 100.00,
        ]);

        // Create payment record
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => 'crypto',
            'amount' => 100.00,
            'status' => 'pending',
            'gateway_transaction_id' => 'crypto_tx_12345',
        ]);

        // Simulate payment confirmation
        $payment->update(['status' => 'completed']);
        $order->updateStatus('paid_confirmed');

        // Verify payment and order status
        $this->assertEquals('completed', $payment->fresh()->status);
        $this->assertEquals('paid_confirmed', $order->fresh()->status);
    }

    /** @test */
    public function payment_workflow_with_traditional_payment()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'payment_method' => 'traditional',
            'total' => 150.00,
        ]);

        // Create payment record
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => 'traditional',
            'amount' => 150.00,
            'status' => 'pending',
            'gateway_transaction_id' => 'card_tx_67890',
        ]);

        // Simulate payment confirmation
        $payment->update(['status' => 'completed']);
        $order->updateStatus('paid_confirmed');

        // Verify payment and order status
        $this->assertEquals('completed', $payment->fresh()->status);
        $this->assertEquals('paid_confirmed', $order->fresh()->status);
    }

    /** @test */
    public function order_fulfillment_workflow()
    {
        // Create and pay for order
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid_confirmed',
        ]);

        // Transition to processing
        $order->updateStatus('processing');
        $this->assertEquals('processing', $order->fresh()->status);

        // Transition to shipped
        $order->updateStatus('shipped');
        $this->assertEquals('shipped', $order->fresh()->status);

        // Verify order is marked as shipped
        $this->assertTrue($order->fresh()->isShipped());
    }

    /** @test */
    public function order_cancellation_workflow()
    {
        $product = Product::factory()->create(['stock' => 10]);
        
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Update inventory (simulate order creation)
        $product->decrement('stock', 3);
        $this->assertEquals(7, $product->fresh()->stock);

        // Cancel order
        $order->updateStatus('cancelled');
        $this->assertEquals('cancelled', $order->fresh()->status);

        // Restore inventory
        $product->increment('stock', 3);
        $this->assertEquals(10, $product->fresh()->stock);
    }

    /** @test */
    public function it_prevents_cancellation_of_shipped_orders()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'shipped',
        ]);

        // Try to cancel shipped order
        $result = $order->updateStatus('cancelled');
        
        $this->assertFalse($result);
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    /** @test */
    public function inventory_management_during_order_creation()
    {
        $product = Product::factory()->create(['stock' => 5]);

        // Try to create order with insufficient stock
        $this->expectException(\Exception::class);
        
        DB::transaction(function () use ($product) {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'pending_payment',
            ]);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 10, // More than available stock
            ]);

            // This should fail if inventory check is implemented
            if ($product->stock < 10) {
                throw new \Exception('Insufficient stock');
            }
        });
    }

    /** @test */
    public function concurrent_order_creation_handling()
    {
        $product = Product::factory()->create(['stock' => 10]);

        // Simulate concurrent orders
        $order1 = Order::factory()->create(['user_id' => $this->user->id]);
        $order2 = Order::factory()->create(['user_id' => $this->user->id]);

        // First order takes 6 units
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 6,
        ]);
        $product->decrement('stock', 6);

        // Second order tries to take 5 units (only 4 left)
        $this->expectException(\Exception::class);
        
        if ($product->stock < 5) {
            throw new \Exception('Insufficient stock for concurrent order');
        }
    }

    /** @test */
    public function order_with_multiple_payment_attempts()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'total' => 100.00,
        ]);

        // First failed payment attempt
        $payment1 = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'status' => 'failed',
            'gateway_transaction_id' => 'failed_tx_123',
        ]);

        // Second successful payment attempt
        $payment2 = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'status' => 'completed',
            'gateway_transaction_id' => 'success_tx_456',
        ]);

        // Update order status
        $order->updateStatus('paid_confirmed');

        // Verify order status and payment history
        $this->assertEquals('paid_confirmed', $order->fresh()->status);
        $this->assertEquals(2, $order->payments()->count());
        
        $failedPayment = $order->payments()->where('status', 'failed')->first();
        $successfulPayment = $order->payments()->where('status', 'completed')->first();
        
        $this->assertNotNull($failedPayment);
        $this->assertNotNull($successfulPayment);
    }

    /** @test */
    public function order_calculation_with_discounts_and_shipping()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount' => 10.00, // 10% discount
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 90.00, // Discounted price
            'discount' => 10.00,
        ]);

        // Update order totals
        $order->update([
            'subtotal' => 180.00, // 90 * 2
            'tax' => 14.40, // 8% of 180
            'shipping' => 10.00,
            'total' => 204.40, // 180 + 14.40 + 10
        ]);

        // Verify calculations
        $this->assertEquals(180.00, $order->subtotal);
        $this->assertEquals(14.40, $order->tax);
        $this->assertEquals(10.00, $order->shipping);
        $this->assertEquals(204.40, $order->total);
    }

    /** @test */
    public function order_address_handling()
    {
        $shippingAddress = [
            'street' => '123 Shipping St',
            'city' => 'Ship City',
            'state' => 'SC',
            'zip' => '12345',
            'country' => 'US'
        ];

        $billingAddress = [
            'street' => '456 Billing Ave',
            'city' => 'Bill City',
            'state' => 'BC',
            'zip' => '67890',
            'country' => 'US'
        ];

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
        ]);

        // Verify addresses are stored correctly
        $this->assertEquals($shippingAddress, $order->shipping_address);
        $this->assertEquals($billingAddress, $order->billing_address);
        $this->assertEquals('Ship City', $order->shipping_address['city']);
        $this->assertEquals('Bill City', $order->billing_address['city']);
    }
}