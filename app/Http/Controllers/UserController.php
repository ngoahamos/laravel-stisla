<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function __construct()
    {

    }

    public function login()
    {
        return view('pages.auth.auth-login');
    }

    public function attempt(Request $request)
    {

        return redirect(route('dashboards.home'));

    }

    public function logout()
    {

        return redirect(route('login'));
    }

}


