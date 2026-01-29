<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(5),
            'image_path' => 'banners/default/banner.jpg', // Will be overridden in seeder
            'image_mobile_path' => null,
            'link_url' => fake()->url(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'starts_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => fake()->optional(0.7)->dateTimeBetween('now', '+2 months'),
        ];
    }
}