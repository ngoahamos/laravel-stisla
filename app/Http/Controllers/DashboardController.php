<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    /**
     * DashboardController constructor.
     */
    public function __construct()
    {

    }

    public function home()
    {
        return view('pages.dashboard.dashboard');
    }

    public function profile()
    {
        $user = User::with([])->find(auth()->id());

        return view('pages.dashboard.profile', ['user' => $user]);
    }

    public function changeMyPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => ['required', 'confirmed',Password::min(8),
                Password::min(8)->letters(),
                Password::min(8)->mixedCase(),
                Password::min(8)->numbers(),
                Password::min(8)->symbols(),
                Password::min(8)->uncompromised()]
        ]);
        $user = User::find(auth()->id());

        if (!Hash::check($request->old_password, $user->getAuthPassword())) {
            return redirect()->back()->with('error_message', 'Wrong Password');
        }
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect(route('logout'))->with('success_message', 'Password Changed Successfully');

    }

    public function changeMyDp(Request $request)
    {
        $request->validate([
            'image' => 'image',
        ]);
        $user = User::find(auth()->id());

        $path = $request->file('image')->store('users');

        $user->raw_picture = $path;
        $user->save();


        return redirect(route('dashboards.profile'))->with('success_message', 'Profile Changed Successfully');




    }
}
