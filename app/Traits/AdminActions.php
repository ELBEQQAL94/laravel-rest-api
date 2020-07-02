<?php

namespace App\Traits;


trait AdminActions {
    /**
     * Determine whether the user can view the model.
     *
     * @param  $user
     * @param  $ability
     * @return boolean
     */
    public function before($user, $ability)
    {
        if($user->isAdmin()) {
            return true;
        }
    }
}
