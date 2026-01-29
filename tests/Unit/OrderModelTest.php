<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    protected Order $order;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_uses_uuid_as_primary_key()
    {
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $this->order->id);
        $this->assertEquals(36, strlen($this->order->id));
    }

    /** @test */
    public function it_generates_unique_order_number()
    {
        $order2 = Order::factory()->create();
        
        $this->assertNotEmpty($this->order->order_number);
        $this->assertNotEmpty($order2->order_number);
        $this->assertNotEquals($this->order->order_number, $order2->order_number);
    }

    /** @test */
    public function it_has_correct_relationships()
    {
        // User relationship
        $this->assertInstanceOf(User::class, $this->order->user);
        $this->assertEquals($this->user->id, $this->order->user->id);
        
        // Order items relationship
        $orderItem = OrderItem::factory()->create(['order_id' => $this->order->id]);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->order->items);
        $this->assertCount(1, $this->order->items);
        $this->assertInstanceOf(OrderItem::class, $this->order->items->first());
        
        // Payments relationship
        $payment = Payment::factory()->create(['order_id' => $this->order->id]);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->order->payments);
        $this->assertCount(1, $this->order->payments);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $order = Order::factory()->create([
            'subtotal' => 100.50,
            'tax' => 8.04,
            'shipping' => 10.00,
            'total' => 118.54,
            'shipping_address' => ['street' => '123 Main St'],
            'billing_address' => ['street' => '123 Main St'],
        ]);

        $this->assertIsFloat($order->subtotal);
        $this->assertIsFloat($order->tax);
        $this->assertIsFloat($order->shipping);
        $this->assertIsFloat($order->total);
        $this->assertIsArray($order->shipping_address);
        $this->assertIsArray($order->billing_address);
    }

    /** @test */
    public function it_can_transition_to_valid_statuses()
    {
        $order = Order::factory()->create(['status' => 'pending_payment']);
        
        // Valid transitions
        $this->assertTrue($order->canTransitionTo('paid_unconfirmed'));
        $this->assertTrue($order->canTransitionTo('cancelled'));
        
        $order->status = 'paid_unconfirmed';
        $this->assertTrue($order->canTransitionTo('paid_confirmed'));
        $this->assertTrue($order->canTransitionTo('cancelled'));
        
        $order->status = 'paid_confirmed';
        $this->assertTrue($order->canTransitionTo('processing'));
        $this->assertTrue($order->canTransitionTo('cancelled'));
        
        $order->status = 'processing';
        $this->assertTrue($order->canTransitionTo('shipped'));
        $this->assertTrue($order->canTransitionTo('cancelled'));
        
        $order->status = 'shipped';
        $this->assertFalse($order->canTransitionTo('cancelled')); // Can't cancel shipped orders
    }

    /** @test */
    public function it_cannot_transition_to_invalid_statuses()
    {
        $order = Order::factory()->create(['status' => 'pending_payment']);
        
        // Invalid transitions
        $this->assertFalse($order->canTransitionTo('paid_confirmed')); // Must go through paid_unconfirmed first
        $this->assertFalse($order->canTransitionTo('processing'));
        $this->assertFalse($order->canTransitionTo('shipped'));
    }

    /** @test */
    public function it_updates_status_safely()
    {
        $order = Order::factory()->create(['status' => 'pending_payment']);
        
        // Valid transition
        $result = $order->updateStatus('paid_unconfirmed');
        $this->assertTrue($result);
        $this->assertEquals('paid_unconfirmed', $order->fresh()->status);
        
        // Invalid transition
        $result = $order->updateStatus('shipped');
        $this->assertFalse($result);
        $this->assertEquals('paid_unconfirmed', $order->fresh()->status);
    }

    /** @test */
    public function it_calculates_total_from_subtotal_tax_and_shipping()
    {
        $order = Order::factory()->create([
            'subtotal' => 100.00,
            'tax' => 8.00,
            'shipping' => 10.00,
        ]);

        $this->assertEquals(118.00, $order->total);
    }

    /** @test */
    public function it_detects_if_order_is_paid()
    {
        $unpaidOrder = Order::factory()->create(['status' => 'pending_payment']);
        $paidOrder = Order::factory()->create(['status' => 'paid_confirmed']);
        $processingOrder = Order::factory()->create(['status' => 'processing']);

        $this->assertFalse($unpaidOrder->isPaid());
        $this->assertTrue($paidOrder->isPaid());
        $this->assertTrue($processingOrder->isPaid());
    }

    /** @test */
    public function it_detects_if_order_is_cancelled()
    {
        $activeOrder = Order::factory()->create(['status' => 'processing']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);

        $this->assertFalse($activeOrder->isCancelled());
        $this->assertTrue($cancelledOrder->isCancelled());
    }

    /** @test */
    public function it_detects_if_order_is_shipped()
    {
        $unshippedOrder = Order::factory()->create(['status' => 'processing']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);

        $this->assertFalse($unshippedOrder->isShipped());
        $this->assertTrue($shippedOrder->isShipped());
    }

    /** @test */
    public function it_gets_formatted_total()
    {
        $order = Order::factory()->create(['total' => 118.54]);
        
        $this->assertEquals('$118.54', $order->getFormattedTotal());
    }

    /** @test */
    public function it_gets_status_display_name()
    {
        $statuses = [
            'pending_payment' => 'Pending Payment',
            'paid_unconfirmed' => 'Paid (Unconfirmed)',
            'paid_confirmed' => 'Paid (Confirmed)',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'cancelled' => 'Cancelled',
        ];

        foreach ($statuses as $status => $displayName) {
            $order = Order::factory()->create(['status' => $status]);
            $this->assertEquals($displayName, $order->getStatusDisplayName());
        }
    }

    /** @test */
    public function it_gets_status_color()
    {
        $colors = [
            'pending_payment' => 'yellow',
            'paid_unconfirmed' => 'blue',
            'paid_confirmed' => 'green',
            'processing' => 'purple',
            'shipped' => 'success',
            'cancelled' => 'danger',
        ];

        foreach ($colors as $status => $color) {
            $order = Order::factory()->create(['status' => $status]);
            $this->assertEquals($color, $order->getStatusColor());
        }
    }

    /** @test */
    public function it_has_scopes_for_filtering_by_status()
    {
        Order::factory()->create(['status' => 'pending_payment']);
        Order::factory()->create(['status' => 'paid_confirmed']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'cancelled']);

        $this->assertEquals(1, Order::pendingPayment()->count());
        $this->assertEquals(1, Order::paidConfirmed()->count());
        $this->assertEquals(1, Order::processing()->count());
        $this->assertEquals(1, Order::cancelled()->count());
    }

    /** @test */
    public function it_has_scope_for_active_orders()
    {
        Order::factory()->create(['status' => 'pending_payment']);
        Order::factory()->create(['status' => 'paid_confirmed']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'shipped']);
        Order::factory()->create(['status' => 'cancelled']);

        $this->assertEquals(4, Order::active()->count()); // All except cancelled
    }

    /** @test */
    public function it_has_scope_for_paid_orders()
    {
        Order::factory()->create(['status' => 'pending_payment']);
        Order::factory()->create(['status' => 'paid_unconfirmed']);
        Order::factory()->create(['status' => 'paid_confirmed']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'shipped']);
        Order::factory()->create(['status' => 'cancelled']);

        $this->assertEquals(4, Order::paid()->count()); // paid_unconfirmed, paid_confirmed, processing, shipped
    }

    /** @test */
    public function it_handles_address_json_correctly()
    {
        $shippingAddress = [
            'street' => '123 Main St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'US'
        ];

        $billingAddress = [
            'street' => '456 Billing St',
            'city' => 'Billing City',
            'state' => 'BL',
            'zip' => '67890',
            'country' => 'US'
        ];

        $order = Order::factory()->create([
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
        ]);

        $this->assertEquals($shippingAddress, $order->shipping_address);
        $this->assertEquals($billingAddress, $order->billing_address);
        $this->assertEquals('Test City', $order->shipping_address['city']);
        $this->assertEquals('Billing City', $order->billing_address['city']);
    }

    /** @test */
    public function it_generates_order_number_with_correct_format()
    {
        $order = Order::factory()->create();
        
        // Order number should be in format: ORD-YYYYMMDD-XXXX
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $order->order_number);
    }

    /** @test */
    public function it_can_be_soft_deleted_if_needed()
    {
        $order = Order::factory()->create();
        
        $order->delete();
        
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
        $this->assertNotNull($order->deleted_at);
    }
}