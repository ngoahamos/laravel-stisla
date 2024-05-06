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
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user == null) return redirect()->back()->with('error_message', 'Wrong username or password');

        if ($user->status != 'Active')
            return redirect()->back()->with('error_message', 'Your Access is revoked.');


        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();
            $user = Auth::user();

            session()->put('is_login', 'true');
            session()->put('role_name', $user->role_name);

            return redirect(route('dashboards.home'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');

    }

    public function logout()
    {

        session()->forget('is_login');
        session()->forget('role_name');
        Auth::logout();


        return redirect(route('login'));
    }

}


