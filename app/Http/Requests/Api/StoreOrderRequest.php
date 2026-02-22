<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.pieces_per_package' => 'nullable|integer|min:1',
            
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|in:crypto,traditional',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Your cart is empty. Please add items to create an order.',
            'items.min' => 'Your cart is empty. Please add items to create an order.',
            'items.*.product_id.exists' => 'One or more products in your cart are no longer available.',
            'items.*.quantity.max' => 'Maximum quantity per item is 100.',
            'payment_method.in' => 'Invalid payment method selected.',
        ];
    }

    protected function prepareForValidation()
    {
        // If payment_method is not provided, default to traditional
        if (!$this->has('payment_method')) {
            $this->merge([
                'payment_method' => 'traditional',
            ]);
        }
    }

    public function getTotalQuantity()
    {
        return collect($this->items)->sum('quantity');
    }

    public function validateInventory()
    {
        $errors = [];
        
        foreach ($this->items as $index => $item) {
            $product = \App\Models\Product::find($item['product_id']);
            
            if (!$product) {
                $errors["items.{$index}.product_id"] = "Product not found.";
                continue;
            }

            if (!$product->is_active) {
                $errors["items.{$index}.product_id"] = "Product '{$product->title}' is no longer available.";
                continue;
            }

            if ($product->track_inventory && $product->stock_qty < $item['quantity']) {
                $available = $product->stock_qty;
                $errors["items.{$index}.quantity"] = "Only {$available} units available for '{$product->title}'.";
                continue;
            }

            if (!empty($item['size'])) {
                $productSize = $product->size;
                $availableSizes = $product->available_sizes ?? [];
                
                // Only validate if product has a specific size set
                if ($productSize && $productSize !== $item['size']) {
                    $errors["items.{$index}.size"] = "Size '{$item['size']}' not available for '{$product->title}'.";
                }
                // Only validate if product has predefined sizes list
                elseif (!empty($availableSizes) && !in_array($item['size'], $availableSizes)) {
                    $errors["items.{$index}.size"] = "Size '{$item['size']}' not available for '{$product->title}'. Available: " . implode(', ', $availableSizes);
                }
            }
            
            if (!empty($item['color'])) {
                $availableColors = $product->available_colors ?? [];
                
                // Only validate if product has predefined colors list
                if (!empty($availableColors) && !in_array($item['color'], array_column($availableColors, 'name'))) {
                    $errors["items.{$index}.color"] = "Color '{$item['color']}' not available for '{$product->title}'.";
                }
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function getOrderData()
    {
        $user = Auth::user();
        
        $userId = $this->user_id ?? $user->id;
        if ($this->user_id && $user->role !== 'admin') {
            $userId = $user->id;
        }

        $targetUser = \App\Models\User::find($userId);
        
        if (!$targetUser) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => ['User not found.'],
            ]);
        }

        if (empty($targetUser->address) || empty($targetUser->city) || empty($targetUser->country)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => ['Please add a shipping address to your profile before placing an order.'],
            ]);
        }

        $shippingAddress = [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'phone' => $targetUser->phone,
            'address' => $targetUser->address,
            'city' => $targetUser->city,
            'state' => null,
            'postal_code' => $targetUser->postal_code,
            'country' => $targetUser->country,
        ];

        $billingAddress = $shippingAddress;
        $billingAddress['name'] = $this->input('billing_address.name') ?? $shippingAddress['name'];
        
        return [
            'user_id' => $userId,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'notes' => $this->notes,
            'items' => $this->items,
            'payment_method' => $this->payment_method,
        ];
    }
}