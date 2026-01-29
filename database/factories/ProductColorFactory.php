<?php

namespace Database\Factories;

use App\Models\ProductColor;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductColorFactory extends Factory
{
    protected $model = ProductColor::class;

    public function definition(): array
    {
        $colors = [
            ['name' => 'Red', 'hex' => '#FF0000'],
            ['name' => 'Blue', 'hex' => '#0000FF'],
            ['name' => 'Green', 'hex' => '#00FF00'],
            ['name' => 'Yellow', 'hex' => '#FFFF00'],
            ['name' => 'Black', 'hex' => '#000000'],
            ['name' => 'White', 'hex' => '#FFFFFF'],
            ['name' => 'Purple', 'hex' => '#800080'],
            ['name' => 'Orange', 'hex' => '#FFA500'],
            ['name' => 'Pink', 'hex' => '#FFC0CB'],
            ['name' => 'Brown', 'hex' => '#A52A2A'],
            ['name' => 'Gray', 'hex' => '#808080'],
            ['name' => 'Navy', 'hex' => '#000080'],
        ];

        $color = fake()->randomElement($colors);

        return [
            'product_id' => Product::factory(),
            'name' => $color['name'],
            'hex' => $color['hex'],
            'image_path' => fake()->optional(0.3)->filePath(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}