<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

trait GateContract
{
    public function gates()
    {
//        Gate::define('change-password', function(User $user, $user_id) {
//            return $user->isTopLevel() or $user->id == $user_id;
//        });
//
//        Gate::define('super-or-self', function(User $user, $user_id) {
//            return $user->isTopLevel() or $user->id == $user_id;
//        });


    }
}
