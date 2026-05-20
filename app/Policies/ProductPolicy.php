<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view products.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'warehouse', 'manager']);
    }

    /**
     * Determine whether the user can view a specific product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'warehouse', 'manager']);
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'warehouse']);
    }

    /**
     * Determine whether the user can record stock adjustments.
     */
    public function adjustStock(User $user): bool
    {
        return $user->hasRole(['admin', 'warehouse']);
    }
}
