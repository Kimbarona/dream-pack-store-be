<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Category;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function viewAny(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.view');
        }
        return false;
    }

    public function view(User|Admin $user, Category $category): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.view');
        }
        return false;
    }

    public function create(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.create');
        }
        return false;
    }

    public function update(User|Admin $user, Category $category): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.update');
        }
        return false;
    }

    public function delete(User|Admin $user, Category $category): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.delete');
        }
        return false;
    }

    public function restore(User|Admin $user, Category $category): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.update');
        }
        return false;
    }

    public function forceDelete(User|Admin $user, Category $category): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.delete');
        }
        return false;
    }

    public function reorder(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermissionTo('categories.update');
        }
        return false;
    }
}