<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Auth;

trait HasModuleAccess
{
    /**
     * Check if current user can access the resource based on module permissions
     */
    public static function canAccessResource(string $module): bool
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user) {
            return false;
        }

        // Check module access first
        if (!$user->canAccessModule($module)) {
            return false;
        }

        // Then check specific permission
        $permission = $module . '.view';
        return $user->hasPermissionTo($permission);
    }

    /**
     * Get navigation visibility based on module access
     */
    public static function canViewAny(): bool
    {
        // This will be overridden by each resource
        return true;
    }

    /**
     * Check create permission
     */
    public static function canCreate(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo(static::getPermissionName('create'));
    }

    /**
     * Check edit permission
     */
    public static function canEdit($record): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo(static::getPermissionName('update'));
    }

    /**
     * Check delete permission
     */
    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo(static::getPermissionName('delete'));
    }

    /**
     * Check delete any permission
     */
    public static function canDeleteAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo(static::getPermissionName('delete'));
    }

    /**
     * Get permission name for the resource
     */
    protected static function getPermissionName(string $action): string
    {
        $resourceName = strtolower(class_basename(static::class));
        
        // Map resource names to module names
        $moduleMap = [
            'productresource' => 'products',
            'categoryresource' => 'categories',
            'orderresource' => 'orders',
            'bannerresource' => 'banners',
            'adminresource' => 'admins',
            'roleresource' => 'roles',
            'permissionresource' => 'roles', // Permissions use same permission as roles
        ];

        $module = $moduleMap[$resourceName] ?? str_replace('resource', '', $resourceName);
        
        return $module . '.' . $action;
    }
}