<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . fake()->unique()->numberBetween(10000, 99999),
            'user_id' => User::factory(),
            'status' => fake()->randomElement([
                'pending_payment',
                'paid_confirmed', 
                'processing',
                'shipped'
            ]),
            'subtotal' => fake()->randomFloat(2, 50, 500),
            'tax_amount' => fake()->randomFloat(2, 5, 50),
            'shipping_amount' => fake()->randomFloat(2, 5, 20),
            'total' => fn($attributes) => $attributes['subtotal'] + $attributes['tax_amount'] + ($attributes['shipping_amount'] ?? 10.00),
            'shipping_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => fake()->country(),
            ],
            'billing_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => fake()->country(),
            ],
        ];
    }
}