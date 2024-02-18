<?php

namespace App\Scopes;

use App\Contracts\RoleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyIDScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        // if role name is set then there is an authenticated user
        $role_name = session()->get('role_name', null);

        if ($role_name != null)
        {
            if ($role_name == RoleType::$DIRECTOR or $role_name == RoleType::$AGENT or $role_name == RoleType::$MANAGER)
            {
                $company_id = session()->get('company_id');
                $builder->where('id', $company_id);
            }
        }
    }
}
