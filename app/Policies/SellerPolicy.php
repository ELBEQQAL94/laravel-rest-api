<?php

namespace App\Policies;

use App\Traits\AdminActions;
use App\Seller;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SellerPolicy
{
    use HandlesAuthorization, AdminActions;

    /**
     * Determine whether the user can view the seller.
     *
     * @param  \App\User  $user
     * @param  \App\Seller  $seller
     * @return mixed
     */
    public function view(User $user, Seller $seller)
    {
        return $user->id  === $seller->id;
    }

    /**
     * Determine whether the user can sale product.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function sale(User $user, User $seller)
    {
        return $user->id  === $seller->id;
    }

    /**
     * Determine whether the user can update a product.
     *
     * @param  \App\User  $user
     * @param  \App\Seller  $seller
     * @return mixed
     */
    public function update(User $user, Seller $seller)
    {
        return $user->id  === $seller->id;
    }

    /**
     * Determine whether the user can delete a product.
     *
     * @param  \App\User  $user
     * @param  \App\Seller  $seller
     * @return mixed
     */
    public function delete(User $user, Seller $seller)
    {
        return $user->id  === $seller->id;
    }
}
