<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data (PostgreSQL compatible)
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        // Define all permissions
        $permissions = [
            // Administrators
            'admins.view',
            'admins.create', 
            'admins.update',
            'admins.delete',
            'admins.manage',
            
            // Role Management
            'roles.view',
            'roles.create',
            'roles.update', 
            'roles.delete',
            'roles.manage',
            
            // Order Management
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.delete',
            
            // Product Management
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            
            // Category Management
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            
            // Banner Management
            'banners.view',
            'banners.create',
            'banners.update',
            'banners.delete',
            
            // Settings
            'settings.manage',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        // Define roles
        $roles = [
            'Super Admin' => [
                'permissions' => $permissions, // All permissions
            ],
            'Admin' => [
                'permissions' => $permissions, // All permissions
            ],
            'co-admin' => [
                'permissions' => [], // Co-admins get permissions from enabled_modules only
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin',
            ]);
            
            $role->syncPermissions($roleData['permissions']);
            
            $this->command->info("Created role: {$roleName}");
        }

        $this->command->info('✅ Roles and permissions seeded successfully!');
    }
}