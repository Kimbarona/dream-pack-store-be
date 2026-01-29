<?php

namespace Database\Factories;

use App\Models\BannerImage;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerImageFactory extends Factory
{
    protected $model = BannerImage::class;

    public function definition(): array
    {
        return [
            'banner_id' => Banner::factory(),
            'path' => 'banners/default/image.jpg', // Will be overridden in seeder
            'disk' => 'public',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_mobile' => fake()->boolean(20), // 20% chance of being mobile
        ];
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mobile' => true,
        ]);
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mobile' => false,
        ]);
    }
}