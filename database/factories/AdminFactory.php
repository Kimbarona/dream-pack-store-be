<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'is_active' => true,
            'enabled_modules' => [
                'orders' => fake()->randomElement(['view', 'manage', 'none']),
                'products' => fake()->randomElement(['view', 'manage', 'none']),
                'categories' => fake()->randomElement(['view', 'manage', 'none']),
                'banners' => fake()->randomElement(['view', 'manage', 'none']),
                'settings' => fake()->randomElement(['manage', 'none']),
                'admin_users' => fake()->randomElement(['manage', 'none']),
                'role_management' => fake()->randomElement(['manage', 'none']),
            ],
        ];
    }
}