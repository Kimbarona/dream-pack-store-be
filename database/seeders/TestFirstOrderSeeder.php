<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class TestFirstOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Only run in local or when explicitly enabled
        if (app()->environment('production') && !env('SEED_TEST_ORDER', false)) {
            $this->command->error('TestFirstOrderSeeder is disabled in production. Set SEED_TEST_ORDER=true to override.');
            return;
        }

        $this->command->info('Starting TestFirstOrderSeeder...');

        try {
            DB::transaction(function () {
                // Validate prerequisites
                $this->validatePrerequisites();

                // Create test order
                $order = $this->createTestOrder();
                $this->command->info("✓ Created order: {$order->order_number} (ID: {$order->id})");

                // Create order items
                $itemsCount = $this->createOrderItems($order);
                $this->command->info("✓ Created {$itemsCount} order items for order {$order->order_number}");

                // Recalculate order totals to ensure consistency
                $order->recalculateTotals();
                $this->command->info("✓ Order totals recalculated - Subtotal: {$order->subtotal}, Total: {$order->total}");

                // Verify the data
                $this->verifyCreatedData($order);

                $this->command->info('✅ TestFirstOrderSeeder completed successfully!');
            });
        } catch (\Exception $e) {
            $this->command->error("❌ TestFirstOrderSeeder failed: " . $e->getMessage());
            Log::error('TestFirstOrderSeeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function validatePrerequisites(): void
    {
        $this->command->info('Validating prerequisites...');

        // Check user_id = 3 exists
        $user = User::find(3);
        if (!$user) {
            // Create test user with ID 3 if possible
            $this->command->warn('User ID 3 not found. Creating test user...');
            
            // For PostgreSQL, we need to handle sequence properly
            $maxUserId = User::max('id') ?? 0;
            if ($maxUserId < 3) {
                DB::statement("ALTER SEQUENCE users_id_seq RESTART WITH 3");
            }
            
            $user = User::create([
                'id' => 3,
                'name' => 'Test Customer',
                'email' => 'test.customer@example.com',
                'password' => bcrypt('password'),
            ]);
            $this->command->info("✓ Created test user: {$user->name} (ID: {$user->id})");
        } else {
            $this->command->info("✓ Found existing user: {$user->name} (ID: {$user->id})");
        }

        // Check product_id = 1 exists
        $product = Product::find(1);
        if (!$product) {
            // Create test product with ID 1 if possible
            $this->command->warn('Product ID 1 not found. Creating test product...');
            
            // For PostgreSQL, we need to handle sequence properly
            $maxProductId = Product::max('id') ?? 0;
            if ($maxProductId < 1) {
                DB::statement("ALTER SEQUENCE products_id_seq RESTART WITH 1");
            }
            
            $product = Product::create([
                'id' => 1,
                'title' => 'Test Product',
                'slug' => 'test-product',
                'description' => 'A test product for order seeding',
                'price' => 29.99,
                'sku' => 'TEST-001',
                'stock_qty' => 100,
                'is_active' => true,
                'pieces_per_package' => 1,
            ]);
            $this->command->info("✓ Created test product: {$product->title} (ID: {$product->id})");
        } else {
            $this->command->info("✓ Found existing product: {$product->title} (ID: {$product->id})");
        }
    }

    private function createTestOrder(): Order
    {
        $this->command->info('Creating test order...');

        $shippingAddress = [
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'address_line_1' => '123 Test Street',
            'address_line_2' => 'Apt 4B',
            'city' => 'Test City',
            'state' => 'TX',
            'postal_code' => '12345',
            'country' => 'US',
            'phone' => '+1-555-123-4567'
        ];

        return Order::create([
            'user_id' => 3,
            'order_number' => 'TEST-' . date('Ymd-His'),
            'subtotal' => 59.98, // Will be recalculated after items are added
            'tax_amount' => 4.80,
            'shipping_amount' => 10.00,
            'total' => 74.78, // Will be recalculated after items are added
            'status' => 'pending_payment',
            'shipping_address' => $shippingAddress,
            'billing_address' => $shippingAddress,
            'notes' => 'Test order created by TestFirstOrderSeeder',
        ]);
    }

    private function createOrderItems(Order $order): int
    {
        $this->command->info('Creating order items...');

        $product = Product::find(1);
        
        // Create first order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2,
            'size' => 'M',
            'chosen_color' => ['name' => 'Red', 'value' => '#FF0000'],
            'pieces_per_package' => $product->pieces_per_package,
        ]);

        // Create second order item with different configuration
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price * 1,
            'size' => 'L',
            'chosen_color' => ['name' => 'Blue', 'value' => '#0000FF'],
            'pieces_per_package' => $product->pieces_per_package,
        ]);

        return 2; // We created 2 items
    }

    private function verifyCreatedData(Order $order): void
    {
        $this->command->info('Verifying created data...');

        // Verify order exists
        $createdOrder = Order::find($order->id);
        if (!$createdOrder) {
            throw new \Exception("Failed to retrieve created order with ID: {$order->id}");
        }

        // Verify order items exist
        $itemsCount = OrderItem::where('order_id', $order->id)->count();
        if ($itemsCount === 0) {
            throw new \Exception("No order items found for order ID: {$order->id}");
        }

        // Calculate expected totals based on items
        $items = OrderItem::where('order_id', $order->id)->get();
        $calculatedSubtotal = $items->sum('total_price');
        $expectedTotal = $calculatedSubtotal + $order->tax_amount + $order->shipping_amount;

        // Verify totals match
        if (abs($order->subtotal - $calculatedSubtotal) > 0.01) {
            $this->command->warn("⚠️  Order subtotal mismatch: {$order->subtotal} vs calculated {$calculatedSubtotal}");
        }

        if (abs($order->total - $expectedTotal) > 0.01) {
            $this->command->warn("⚠️  Order total mismatch: {$order->total} vs expected {$expectedTotal}");
        }

        $this->command->info("✓ Verification complete:");
        $this->command->info("  - Order ID: {$order->id}");
        $this->command->info("  - Order Number: {$order->order_number}");
        $this->command->info("  - User ID: {$order->user_id}");
        $this->command->info("  - Status: {$order->status}");
        $this->command->info("  - Items Count: {$itemsCount}");
        $this->command->info("  - Subtotal: {$order->subtotal}");
        $this->command->info("  - Tax: {$order->tax_amount}");
        $this->command->info("  - Shipping: {$order->shipping_amount}");
        $this->command->info("  - Total: {$order->total}");
    }
}