<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Product;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.view');
        }
        return false;
    }

    public function view(User|Admin $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.view');
        }
        return false;
    }

    public function create(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.create');
        }
        return false;
    }

    public function update(User|Admin $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.update');
        }
        return false;
    }

    public function delete(User|Admin $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.delete');
        }
        return false;
    }

    public function restore(User|Admin $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.update');
        }
        return false;
    }

    public function forceDelete(User|Admin $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.delete');
        }
        return false;
    }

    public function reorder(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('products.update');
        }
        return false;
    }
}