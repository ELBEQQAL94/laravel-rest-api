<?php

namespace App\Policies;

use App\Traits\AdminActions;
use App\Transaction;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization, AdminActions;

    /**
     * Determine whether the user can view the transaction.
     *
     * @param  \App\User  $user
     * @param  \App\Transaction  $transaction
     * @return mixed
     */
    public function view(User $user, Transaction $transaction)
    {
        // user id
        $userId = $user->id;

        // buyer id
        $buyerId = $transaction->buyer->id;

        // seler id
        $sellerId = $transaction->product->seller->id;

        return $userId === $buyerId || $userId === $sellerId;
    }
}
