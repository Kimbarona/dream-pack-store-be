<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'enabled_modules',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'enabled_modules' => 'array',
        ];
    }

    /**
     * Check if the admin is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope a query to only include active admins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if admin can access a specific module.
     *
     * @param string $module
     * @return bool
     */
    public function canAccessModule(string $module): bool
    {
        // Super admin and admin can access all modules
        if ($this->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        // For co-admins, check enabled modules
        $enabledModules = $this->enabled_modules ?? [];
        return isset($enabledModules[$module]) && $enabledModules[$module] !== 'none';
    }

    /**
     * Get permissions based on enabled modules.
     *
     * @return array
     */
    public function getPermissionsFromEnabledModules(): array
    {
        // Super admin and admin get all permissions
        if ($this->hasRole(['Super Admin', 'Admin'])) {
            return \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
                ->pluck('name')
                ->toArray();
        }

        $permissions = [];
        $enabledModules = $this->enabled_modules ?? [];

        foreach ($enabledModules as $module => $access) {
            if ($access === 'none') {
                continue;
            }

            switch ($module) {
                case 'orders':
                    if ($access === 'view') {
                        $permissions[] = 'orders.view';
                    } elseif ($access === 'manage') {
                        $permissions = array_merge($permissions, [
                            'orders.view',
                            'orders.create',
                            'orders.update',
                            'orders.delete'
                        ]);
                    }
                    break;

                case 'products':
                    if ($access === 'view') {
                        $permissions[] = 'products.view';
                    } elseif ($access === 'manage') {
                        $permissions = array_merge($permissions, [
                            'products.view',
                            'products.create',
                            'products.update'
                        ]);
                    }
                    break;

                case 'categories':
                    if ($access === 'view') {
                        $permissions[] = 'categories.view';
                    } elseif ($access === 'manage') {
                        $permissions = array_merge($permissions, [
                            'categories.view',
                            'categories.create',
                            'categories.update'
                        ]);
                    }
                    break;

                case 'banners':
                    if ($access === 'view') {
                        $permissions[] = 'banners.view';
                    } elseif ($access === 'manage') {
                        $permissions = array_merge($permissions, [
                            'banners.view',
                            'banners.create',
                            'banners.update',
                            'banners.delete'
                        ]);
                    }
                    break;

                case 'settings':
                    if ($access === 'manage') {
                        $permissions[] = 'settings.manage';
                    }
                    break;

                case 'admin_users':
                    if ($access === 'manage') {
                        $permissions[] = 'admins.manage';
                    }
                    break;

                case 'role_management':
                    if ($access === 'manage') {
                        $permissions[] = 'roles.manage';
                    }
                    break;
            }
        }

        return array_unique($permissions);
    }

    /**
     * Sync permissions based on enabled modules.
     *
     * @return void
     */
    public function syncPermissionsFromEnabledModules(): void
    {
        $permissions = $this->getPermissionsFromEnabledModules();
        $this->syncPermissions($permissions);
    }

    /**
     * Get default enabled modules for co-admin role.
     *
     * @return array
     */
    public static function getDefaultEnabledModules(): array
    {
        return [
            'orders' => 'view',
            'products' => 'manage',
            'categories' => 'manage',
            'banners' => 'none',
            'settings' => 'none',
            'admin_users' => 'none',
            'role_management' => 'none',
        ];
    }
}