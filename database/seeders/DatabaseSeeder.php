<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            CustomerSeeder::class,
            CatalogSeeder::class,
            BannerSeeder::class,
            SettingsSeeder::class,
        ]);

        // Create a test customer user
        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);

        // Skip test order seeder in Docker
        // if (app()->environment('local') || env('SEED_TEST_ORDER', false)) {
        //     $this->call(TestFirstOrderSeeder::class);
        // }
    }
}
