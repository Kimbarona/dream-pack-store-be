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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string|max:255',
            'shipping_address.last_name' => 'required|string|max:255',
            'shipping_address.email' => 'required|email|max:255',
            'shipping_address.phone' => 'required|string|max:50',
            'shipping_address.address' => 'required|string|max:500',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.state' => 'required|string|max:255',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:2',
            
            'billing_address' => 'nullable|array',
            'billing_address.first_name' => 'required_with:billing_address|string|max:255',
            'billing_address.last_name' => 'required_with:billing_address|string|max:255',
            'billing_address.email' => 'required_with:billing_address|email|max:255',
            'billing_address.phone' => 'required_with:billing_address|string|max:50',
            'billing_address.address' => 'required_with:billing_address|string|max:500',
            'billing_address.city' => 'required_with:billing_address|string|max:255',
            'billing_address.state' => 'required_with:billing_address|string|max:255',
            'billing_address.postal_code' => 'required_with:billing_address|string|max:20',
            'billing_address.country' => 'required_with:billing_address|string|max:2',
            
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|string|in:crypto,traditional',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Your cart is empty. Please add items to create an order.',
            'items.min' => 'Your cart is empty. Please add items to create an order.',
            'items.*.product_id.exists' => 'One or more products in your cart are no longer available.',
            'items.*.quantity.max' => 'Maximum quantity per item is 100.',
            'shipping_address.required' => 'Shipping address is required.',
            'payment_method.in' => 'Invalid payment method selected.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->billing_address === null) {
            $this->merge([
                'billing_address' => $this->shipping_address,
            ]);
        }
    }

    public function getBillingAddress()
    {
        return $this->billing_address ?? $this->shipping_address;
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

            if (!empty($item['size']) && $product->size !== $item['size']) {
                $errors["items.{$index}.size"] = "Size '{$item['size']}' not available for '{$product->title}'.";
                continue;
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function getOrderData()
    {
        return [
            'user_id' => Auth::id(),
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->getBillingAddress(),
            'notes' => $this->notes,
            'items' => $this->items,
            'payment_method' => $this->payment_method,
        ];
    }
}