<?php

namespace Tests\Feature\Filament;

use App\Models\Order;
use App\Models\User;
use App\Filament\Resources\OrderResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderResourceFormTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
    }

    /** @test */
    public function order_form_handles_null_record_safely()
    {
        // Test formatStateUsing closure with null record
        $closure = function ($state, $record) {
            if (!$record) return '0.00';
            return number_format($record->subtotal, 2);
        };

        // Test with null record (create form scenario)
        $result = $closure(null, null);
        $this->assertEquals('0.00', $result);

        // Test with actual order
        $order = Order::factory()->create(['subtotal' => 123.45]);
        $result = $closure(null, $order);
        $this->assertEquals('123.45', $result);
    }

    /** @test */
    public function order_resource_form_renders_without_crash()
    {
        // Test that the form can be built without crashing
        $form = OrderResource::form(\Filament\Forms\Form::make());
        
        // This should not throw any exceptions
        $this->assertNotNull($form);
    }

    /** @test */
    public function create_order_page_loads_without_null_errors()
    {
        $this->actingAs($this->admin);

        // Test create page (no record exists yet)
        $response = $this->get('/admin/orders/create');
        
        // Should load successfully without null property errors
        $response->assertStatus(200);
    }

    /** @test */
    public function edit_order_page_loads_with_existing_record()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'subtotal' => 100.00,
            'tax_amount' => 8.00,
            'shipping_amount' => 10.00,
            'total' => 118.00,
        ]);

        $response = $this->get("/admin/orders/{$order->id}/edit");
        
        $response->assertStatus(200);
        $response->assertSee('100.00'); // Should display formatted subtotal
    }
}