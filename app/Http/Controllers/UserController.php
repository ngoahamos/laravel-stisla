<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\IDType;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private UserService $userService;
    private ActivityService $activityService;

    /**
     * @param UserService $userService
     * @param ActivityService $activityService
     */
    public function __construct(UserService $userService, ActivityService $activityService)
    {
        $this->userService = $userService;
        $this->activityService = $activityService;
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

        $user = User::where('username', $request->username)->first();

        if ($user == null) return redirect()->back()->with('error_message', 'Wrong username or password');

        if ($user->status != 'Active')
            return redirect()->back()->with('error_message', 'Your Access is revoked.');


        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();
            $user = Auth::user();

            session()->put('company_id', $user->company_id);
            session()->put('branch_id', $user->branch_id);
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
        $this->activityService->addActivity(['company_id' => auth_company_id(), 'branch_id' => auth_branch_id(),
            'user_id' => auth_id(),
            'action' => 'Logged out']);

        session()->forget('company_id');
        session()->forget('branch_id');
        session()->forget('is_login');
        session()->forget('role_name');
        session()->forget('_transaction_customer_id');
        Auth::logout();

        // flash message from previous route
        $_previous_message = session()->get('success_message');
        if ($_previous_message != null) {
            session()->flash('success_message', $_previous_message);
        }

        return redirect(route('login'));
    }

    public function agents()
    {
        if(!Gate::allows('top-level')) return redirect()->back()->with('error_message','Access Denied');
        $name = \request()->name;
        $branch_id = \request()->branch_id;
        $status = \request()->status;

        $agents = User::when($name != null, function ($q) use($name) {
            $name = '%' . $name . '%';
            return $q->where('name', 'like', $name)->orWhere('username', 'like', $name)
                ->where('role_name', 'like', $name);
        })
            ->when($branch_id != null, function ($q) use($branch_id) {
                return $q->where('branch_id', $branch_id);
            })
            ->when($status != null, function ($q) use($status) {
                return $q->where('status', $status);
            })->paginate(10)->withQueryString();

        $branches = Branch::all()->pluck('name','id');
        $statuses = user_statuses();
        return view('pages.users.agents', compact('agents','name','status','branch_id',
            'branches', 'statuses'));
    }

    public function createAgent()
    {
        if(!Gate::allows('super-or-director')) return redirect()->back()->with('error_message','Access Denied');

        $companies = [];

        $branches = Branch::all()->pluck('name', 'id');
        if (Gate::allows('super')) {
            $companies = Company::all()->pluck('name', 'id');
        }
        $id_types = IDType::all()->pluck('name', 'id');
        $levels = Gate::allows('super') ? super_levels() : non_super_levels();

        return view('pages.users.create', compact('companies', 'branches','id_types',
            'levels'));
    }

    public function editAgent($id)
    {
        if(!Gate::allows('top-level')) return redirect()->back()->with('error_message','Access Denied');

        $agent = User::with('guarantor')->find($id);

        if ($agent == null) return redirect()->back()->with('error_message','Agent Not Found.');

        $companies = [];

        $branches = Branch::all()->pluck('name', 'id');
        if (Gate::allows('super')) {
            $companies = Company::all()->pluck('name', 'id');
        }
        $id_types = IDType::all()->pluck('name', 'id');
        $levels = Gate::allows('super') ? super_levels() : non_super_levels();

        return view('pages.users.edit', compact('companies', 'branches','id_types',
            'levels', 'agent'));
    }

    public function storeAgent(Request $request)
    {
        if(!Gate::allows('super-or-director')) return redirect()->back()->with('error_message','Access Denied');
        $request->validate([
            'password' => 'required|min:6,confirm',
            'username'=>'required|unique:users',
            'name' => 'required|min:3',
            'role_name' => 'required']);

        $response = $this->userService->addUser($request->toArray());

        if (!$response->status) {
            return redirect()->back()->with('error_message', $response->errors['message']);
        }

        $user = $response->data;

        if ($request->has('file')) {
            $request->merge(['user_id' => $user->id]);
            $this->userService->addPicture($request);
        }

        return redirect(route('managements.agents'))->with('success_message','Agent Added Successfully');

    }

    public function updateAgent($id, Request $request)
    {
        if(!Gate::allows('top-level')) return redirect()->back()->with('error_message','Access Denied');

        $agent = User::find($id);

        if ($agent == null) return redirect()->back()->with('error_message','Agent Not Found.');

        $response = $this->userService->updateUser($id, $request->toArray());

        if (!$response->status) {
            return redirect()->back()->with('error_message', $response->data['message']);
        }

        if ($request->has('file')) {
            $request->merge(['user_id' => $id]);
            $this->userService->addPicture($request);
        }

        return redirect(route('managements.agents'))->with('success_message','Agent Updated Successfully');
    }

    public function changeAgentPassword(Request $request)
    {
        if(!Gate::allows('top-level')) return redirect()->back()->with('error_message','Access Denied');

        $request->validate([
            'agent_id' => 'required',
            'password' => ['required', 'confirmed',Password::min(8),
                Password::min(8)->letters(),
                Password::min(8)->mixedCase(),
                Password::min(8)->numbers(),
                Password::min(8)->symbols(),
                Password::min(8)->uncompromised()]
        ]);
        $user = User::find($request->agent_id);

        $user->password = bcrypt($request->password);
        $user->save();

        $this->activityService->addActivity(['company_id' => auth_company_id(), 'branch_id' => auth_branch_id(),
            'user_id' => auth_id(),
            'action' => 'Changed Officer [' . $user->name . '] Password' ]);


        return redirect()->back()->with('success_message', 'Password Changed Successfully');

    }

    public function changeAgentStatus(Request $request)
    {
        if(!Gate::allows('top-level')) return redirect()->back()->with('error_message','Access Denied');

        $request->validate([
            'user_id' => 'required',
            '_status' => ['required', Rule::in(['0','1','2'])]]);




        $user = User::find($request->user_id);

        if (empty($user)) return redirect()->back()->with('error_message', 'User Not Found.');


        $user->status = $request->_status;
        $user->save();

        $this->activityService->addActivity(['company_id' => auth_company_id(), 'branch_id' => auth_branch_id(),
            'user_id' => auth_id(),
            'action' => 'Changed Officer [' . $user->name . '] Status' ]);



        return redirect()->back()->with('success_message', 'User Status Changed Successfully');

    }
}
