<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

trait GateContract
{
    public function gates()
    {
        Gate::define('change-password', function(User $user, $user_id) {
            return $user->isTopLevel() or $user->id == $user_id;
        });

        Gate::define('super-or-self', function(User $user, $user_id) {
            return $user->isTopLevel() or $user->id == $user_id;
        });

        Gate::define('top-level', function(User $user) {
            return $user->isTopLevel();
        });

        Gate::define('self', function(User $user, $user_id) {
            return $user->id == $user_id;
        });


        Gate::define('director', function (User $user) {
            return $user->isDirector();
        });

        Gate::define('super', function (User $user) {
            return $user->isSuper();
        });

        Gate::define('super-or-director', function (User $user) {
            return $user->hasAnyRole(['super', 'director']);
        });

        Gate::define('manager', function (User $user) {
            return $user->isManager();
        });

        Gate::define('same-company', function (User $user, $company_id) {
            return $user->company_id == $company_id;
        });

        Gate::define('same-branch', function (User $user, $branch_id) {
            return $user->branch_id == $branch_id;
        });

        Gate::define('same-company-and-branch', function (User $user, $company_id, $branch_id) {
            return ($user->branch_id == $branch_id) && ($user->company_id == $company_id);
        });

        Gate::define('agent', function (User $user) {
            return $user->isAgent();
        });


        Gate::before(function (User $user, $ability) {
            return $user->isSuper() ? true: null;
        });
    }
}
