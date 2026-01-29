<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Concerns\HasBackAction;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    use HasBackAction;
    
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // Extract order items from the form data
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            // Generate order number if not provided
            if (empty($data['order_number'])) {
                $data['order_number'] = 'ORD-' . strtoupper(uniqid());
            }

            // Set default status
            if (empty($data['status'])) {
                $data['status'] = 'pending_payment';
            }

            // Set default financial values
            $data['subtotal'] = $data['subtotal'] ?? 0;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['shipping_amount'] = $data['shipping_amount'] ?? 0;
            $data['total'] = $data['total'] ?? 0;

            // Handle nested shipping address fields
            $shippingAddress = [];
            $addressFields = ['address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'phone'];
            
            foreach ($addressFields as $field) {
                if (isset($data["shipping_address.{$field}"])) {
                    $shippingAddress[$field] = $data["shipping_address.{$field}"];
                    unset($data["shipping_address.{$field}"]);
                }
            }
            
            // Set default shipping address if missing required fields
            if (empty($shippingAddress['address_line_1'])) $shippingAddress['address_line_1'] = '123 Default Address';
            if (empty($shippingAddress['city'])) $shippingAddress['city'] = 'Default City';
            if (empty($shippingAddress['state'])) $shippingAddress['state'] = 'CA';
            if (empty($shippingAddress['postal_code'])) $shippingAddress['postal_code'] = '12345';
            if (empty($shippingAddress['country'])) $shippingAddress['country'] = 'US';
            
            $data['shipping_address'] = $shippingAddress;

            // Create the order
            $order = static::getModel()::create($data);

            // Create order items if any
            foreach ($itemsData as $itemData) {
                if (isset($itemData['product_id']) && !empty($itemData['product_id'])) {
                    // Get product information to ensure unit_price is set
                    $product = \App\Models\Product::find($itemData['product_id']);
                    
                    // Ensure required fields are set
                    $itemData['order_id'] = $order->id;
                    
                    // Always set unit_price from product (since form field is disabled)
                    $itemData['unit_price'] = $product ? $product->price : 0;
                    
                    // Ensure product_title is set
                    $itemData['product_title'] = $itemData['product_title'] ?? ($product ? $product->title : 'Unknown Product');
                    
                    // Ensure product_sku is set
                    $itemData['product_sku'] = $itemData['product_sku'] ?? ($product ? $product->sku : 'UNKNOWN');
                    
                    // Auto-calculate total price if not set or if quantity/price changed
                    $quantity = (int) ($itemData['quantity'] ?? 1);
                    $unitPrice = (float) ($itemData['unit_price'] ?? 0);
                    $itemData['total_price'] = $unitPrice * $quantity;

                    // Set default pieces_per_package if not set
                    if (!isset($itemData['pieces_per_package']) || $itemData['pieces_per_package'] === null) {
                        $itemData['pieces_per_package'] = $product?->pieces_per_package ?? 1;
                    }

                    // Handle color field (convert string to array if needed)
                    if (isset($itemData['chosen_color']) && is_string($itemData['chosen_color'])) {
                        $itemData['chosen_color'] = ['name' => $itemData['chosen_color'], 'value' => $itemData['chosen_color']];
                    }

                    // Clean up any empty fields to avoid null violations
                    foreach (['size', 'chosen_color'] as $field) {
                        if (!isset($itemData[$field]) || $itemData[$field] === '') {
                            $itemData[$field] = null;
                        }
                    }

                    // Create the order item
                    \App\Models\OrderItem::create($itemData);
                }
            }

            // Recalculate order totals based on items
            $order->recalculateTotals();

            return $order;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}