<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a super admin account
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Change this in production
            'is_active' => true,
        ]);

        $this->command->info('✓ Super admin account created successfully.');
        $this->command->info('  Email: admin@example.com');
        $this->command->info('  Password: password');
        $this->command->warn('  Please change the password in production!');
    }
}