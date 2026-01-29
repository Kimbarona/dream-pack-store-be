<?php

namespace Database\Factories;

use App\Models\ProductSize;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductSizeFactory extends Factory
{
    protected $model = ProductSize::class;

    public function definition(): array
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL'];
        
        return [
            'product_id' => Product::factory(),
            'value' => fake()->randomElement($sizes),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}