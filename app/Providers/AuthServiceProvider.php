<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Category;
use App\Models\Product;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('admin', function ($user) {
            if ($user instanceof \App\Models\Admin) {
                return $user->hasPermissionTo('admins.manage');
            }
            return isset($user->role) && $user->role === 'admin';
        });

        Gate::define('customer', function ($user) {
            return isset($user->role) && $user->role === 'customer';
        });
    }
}
