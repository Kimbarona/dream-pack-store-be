<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-super {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Enter admin email');
        $password = $this->argument('password') ?: $this->secret('Enter admin password');

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        // Check if admin already exists
        if (Admin::where('email', $email)->exists()) {
            $this->error('Admin with this email already exists!');
            return 1;
        }

        // Create admin
        $admin = Admin::create([
            'name' => $this->ask('Enter admin name', 'Super Admin'),
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $this->info('✓ Super admin account created successfully!');
        $this->info('  Name: ' . $admin->name);
        $this->info('  Email: ' . $admin->email);
        $this->info('  Password: ' . str_repeat('*', strlen($password)));

        return 0;
    }
}