<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_admins' => [
                'name' => 'manage_admins',
                'guard_name' => 'admin',
                'group' => 'Administrators',
            ],
            'manage_products' => [
                'name' => 'manage_products',
                'guard_name' => 'admin',
                'group' => 'Product Management',
            ],
            'manage_orders' => [
                'name' => 'manage_orders',
                'guard_name' => 'admin',
                'group' => 'Order Management',
            ],
            'view_reports' => [
                'name' => 'view_reports',
                'guard_name' => 'admin',
                'group' => 'Reports',
            ],
        ];

        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'guard_name' => 'admin',
            ],
            'admin' => [
                'name' => 'Admin',
                'guard_name' => 'admin',
            ],
        ];

        // Create permissions
        foreach ($permissions as $permissionKey => $permissionData) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionData['name'],
                'guard_name' => $permissionData['guard_name'],
            ]);

            if (isset($permissionData['group'])) {
                $permission->group = $permissionData['group'];
                $permission->save();
            }

            $this->command->info("Created permission: {$permissionData['name']}");
        }

        // Create roles
        foreach ($roles as $roleKey => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'],
            ]);

            $this->command->info("Created role: {$roleData['name']}");
        }

        // Assign permissions to roles
        foreach ($roles as $roleKey => $roleData) {
            $role = Role::where('name', $roleData['name'])->first();
            
            if ($role) {
                // Assign permissions based on role
                if ($roleKey === 'super-admin') {
                    // Super admin gets all permissions
                    $allPermissions = Permission::all()->pluck('name');
                    $role->syncPermissions($allPermissions);
                    $this->command->info("Assigned all permissions to role {$roleData['name']}");
                } elseif ($roleKey === 'admin') {
                    // Admin gets limited permissions
                    $adminPermissions = ['manage_products', 'manage_orders'];
                    $role->syncPermissions($adminPermissions);
                    $this->command->info("Assigned limited permissions to role {$roleData['name']}");
                }
            }
        }

        // Create super admin user
        $superAdminRole = Role::where('name', 'super-admin')->first();
        $superAdmin = \App\Models\Admin::firstOrCreate([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('superadmin'),
            'is_active' => true,
        ]);

        if ($superAdminRole) {
            $superAdmin->syncPermissions(
                collect(array_keys($permissions, ['manage_admins', 'manage_products', 'manage_orders', 'view_reports']))
            );
            $superAdmin->assignRole($superAdminRole);
        }

        // Create regular admin user
        $adminRole = Role::where('name', 'admin')->first();
        $admin = \App\Models\Admin::firstOrCreate([
            'name' => 'Regular Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
            'is_active' => true,
        ]);

        if ($adminRole) {
            $admin->syncPermissions(
                collect(array_keys($permissions, ['manage_products', 'manage_orders']))
            );
            $admin->assignRole($adminRole);
        }

        $this->command->info('✅ Roles and permissions seeded successfully!');
        $this->command->info('📧 Admin users created:');
        $this->command->info('  - super-admin@example.com (Super Admin)');
        $this->command->info('  - admin@example.com (Regular Admin)');
    }
}