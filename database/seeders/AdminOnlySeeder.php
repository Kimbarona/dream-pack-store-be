<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        $adminUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@dreampack.com',
                'password' => Hash::make('superadmin123'),
                'role' => 'Super Admin',
                'enabled_modules' => null, // Super admins don't need enabled_modules
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@dreampack.com', 
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                'enabled_modules' => null, // Admins don't need enabled_modules
            ],
            [
                'name' => 'Co-Admin',
                'email' => 'coadmin@dreampack.com',
                'password' => Hash::make('coadmin123'),
                'role' => 'co-admin',
                'enabled_modules' => Admin::getDefaultEnabledModules(),
            ],
        ];

        foreach ($adminUsers as $userData) {
            $admin = Admin::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'is_active' => true,
                    'enabled_modules' => $userData['enabled_modules'],
                ]
            );

            $role = Role::where('name', $userData['role'])->first();
            if ($role && !$admin->hasRole($role)) {
                $admin->assignRole($role);
            }

            // Sync permissions for co-admins based on enabled_modules
            if ($userData['role'] === 'co-admin') {
                $admin->syncPermissionsFromEnabledModules();
            }

            $this->command->info("✓ Created admin: {$userData['email']} ({$userData['role']})");
        }

        $this->command->info('✅ Admin accounts created successfully!');
        $this->command->info('');
        $this->command->info('📧 Admin Login Credentials:');
        $this->command->info('  Super Admin: superadmin@dreampack.com / superadmin123');
        $this->command->info('  Admin: admin@dreampack.com / admin123'); 
        $this->command->info('  Co-Admin: coadmin@dreampack.com / coadmin123');
        $this->command->warn('  ⚠️  Please change passwords in production!');
    }
}