<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(3),
            'price' => fake()->randomFloat(2, 10, 500),
            'sale_price' => fake()->optional(0.3)->randomFloat(2, 5, 100),
            'sku' => fake()->unique()->bothify('########'),
            'stock_qty' => fake()->numberBetween(0, 100),
            'track_inventory' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'meta_title' => fake()->sentence(3),
            'meta_description' => fake()->sentence(10),
            'pieces_per_package' => fake()->numberBetween(1, 10),
        ];
    }
}