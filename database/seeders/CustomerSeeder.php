<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    /**
     * Run database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Main Street, Apt 4B',
                'city' => 'New York',
                'country' => 'United States',
                'postal_code' => '10001',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Emma Johnson',
                'email' => 'emma.johnson@example.com',
                'phone' => '+1 (555) 234-5678',
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'country' => 'United States',
                'postal_code' => '90001',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@example.com',
                'phone' => '+1 (555) 345-6789',
                'address' => '789 Pine Road',
                'city' => 'San Francisco',
                'country' => 'United States',
                'postal_code' => '94102',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
                'phone' => '+1 (555) 456-7890',
                'address' => '321 Elm Street',
                'city' => 'Chicago',
                'country' => 'United States',
                'postal_code' => '60601',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'David Martinez',
                'email' => 'david.martinez@example.com',
                'phone' => '+1 (555) 567-8901',
                'address' => '654 Maple Drive',
                'city' => 'Houston',
                'country' => 'United States',
                'postal_code' => '77001',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@example.com',
                'phone' => '+1 (555) 678-9012',
                'address' => '987 Cedar Lane',
                'city' => 'Phoenix',
                'country' => 'United States',
                'postal_code' => '85001',
                'role' => 'customer',
                'is_active' => false, // Inactive customer for testing
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('users')->insert($customers);
        
        $this->command->info('Sample customers created successfully!');
    }
}
