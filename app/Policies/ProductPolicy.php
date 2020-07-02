<?php

namespace App\Policies;

use App\Traits\AdminActions;
use App\Product;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization, AdminActions;

    /**
     * Determine whether the user can create category.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function addCategory(User $user, Product $product)
    {
        $userId = $user->id;
        $sellerId = $product->seller->id;
        return $userId === $sellerId;
    }

    /**
     * Determine whether the user can delete category.
     *
     * @param  \App\User  $user
     * @param  \App\Product  $product
     * @return mixed
     */
    public function deleteCategory(User $user, Product $product)
    {
        $userId = $user->id;
        $sellerId = $product->seller->id;
        return $userId === $sellerId;
    }
}
