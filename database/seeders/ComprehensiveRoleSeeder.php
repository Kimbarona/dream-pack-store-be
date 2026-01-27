<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ComprehensiveRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        // Define all permissions
        $permissions = [
            // Administrators
            'admins.view' => 'View Admin Users',
            'admins.create' => 'Create Admin Users',
            'admins.update' => 'Update Admin Users',
            'admins.delete' => 'Delete Admin Users',
            'admins.manage' => 'Manage Admin Users',
            
            // Role Management
            'roles.view' => 'View Roles',
            'roles.create' => 'Create Roles',
            'roles.update' => 'Update Roles',
            'roles.delete' => 'Delete Roles',
            'roles.manage' => 'Manage Roles',
            
            // Order Management
            'orders.view' => 'View Orders',
            'orders.create' => 'Create Orders',
            'orders.update' => 'Update Orders',
            'orders.delete' => 'Delete Orders',
            
            // Product Management
            'products.view' => 'View Products',
            'products.create' => 'Create Products',
            'products.update' => 'Update Products',
            'products.delete' => 'Delete Products',
            
            // Category Management
            'categories.view' => 'View Categories',
            'categories.create' => 'Create Categories',
            'categories.update' => 'Update Categories',
            'categories.delete' => 'Delete Categories',
            
            // Banner Management
            'banners.view' => 'View Banners',
            'banners.create' => 'Create Banners',
            'banners.update' => 'Update Banners',
            'banners.delete' => 'Delete Banners',
            
            // Settings
            'settings.view' => 'View Settings',
            'settings.manage' => 'Manage Settings',
        ];

        // Create permissions
        foreach ($permissions as $key => $name) {
            Permission::firstOrCreate([
                'name' => $key,
                'guard_name' => 'admin',
            ]);
            $this->command->info("Created/updated permission: {$key}");
        }

        // Define roles
        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'permissions' => array_keys($permissions), // All permissions
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Admin with most permissions',
                'permissions' => array_keys($permissions), // All permissions
            ],
            'co-admin' => [
                'name' => 'co-admin',
                'description' => 'Limited admin access - permissions from enabled_modules only',
                'permissions' => [], // Co-admins get permissions from enabled_modules only
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleKey => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => 'admin',
            ]);
            
            $role->syncPermissions($roleData['permissions']);
            
            $this->command->info("Created/updated role: {$roleData['name']} with " . count($roleData['permissions']) . " permissions");
        }

        // Create admin users if they don't exist
        $adminUsers = [
            'superadmin@example.com' => [
                'name' => 'Super Admin',
                'password' => bcrypt('superadmin'),
                'role' => 'Super Admin',
                'enabled_modules' => null, // Super admins don't need enabled_modules
            ],
            'admin@example.com' => [
                'name' => 'Admin',
                'password' => bcrypt('admin'),
                'role' => 'Admin',
                'enabled_modules' => null, // Admins don't need enabled_modules
            ],
            'coadmin@example.com' => [
                'name' => 'Co-Admin',
                'password' => bcrypt('coadmin'),
                'role' => 'co-admin',
                'enabled_modules' => \App\Models\Admin::getDefaultEnabledModules(),
            ],
        ];

        foreach ($adminUsers as $email => $userData) {
            $admin = \App\Models\Admin::updateOrCreate(
                ['email' => $email],
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

            $this->command->info("Created/updated admin user: {$email} ({$userData['role']})");
        }

        $this->command->info('✅ Comprehensive roles and permissions seeded successfully!');
        $this->command->info('📧 Admin users:');
        $this->command->info('  - superadmin@example.com (Super Admin) / password: superadmin');
        $this->command->info('  - admin@example.com (Admin) / password: admin');
        $this->command->info('  - coadmin@example.com (Co-Admin) / password: coadmin');
    }
}