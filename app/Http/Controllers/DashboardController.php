<?php

namespace App\Http\Controllers;

use App\Contracts\GetRequestData;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    use GetRequestData;
    /**
     * @var DashboardService
     */
    private $dashboardService;

    /**
     * DashboardController constructor.
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function home()
    {
        $user_id = \request()->user()->isAgent() ? auth_id() : null;
        $result = $this->dashboardService->numberAnalytics($user_id);



        return view('pages.dashboard.dashboard', $result->data);
    }

    public function numberAnalytics()
    {
        $user_id = \request()->user()->isAgent() ? auth_id() : null;

        return helper_response($this->dashboardService->numberAnalytics($user_id));
    }

    public function transactionAnalytics()
    {
        $user_id = \request()->user()->isAgent() ? auth_id() : null;
        return helper_response($this->dashboardService->transactionAnalytics($user_id));
    }

    public function barTransactions()
    {
        $search['branch_id'] = $this->getBranchId();
        $search['dates'] = $this->getDateRangeRequest();
        $search['interval'] = $this->getRequestInterval();

        return helper_response($this->dashboardService->barTransactions($search));
    }

    public function agentTransactions()
    {
        $search['dates'] = get_dates();

        return helper_response($this->dashboardService->agentTransaction($search));
    }

    public function branchTransactions()
    {
        $search['dates'] = get_dates();

        return helper_response($this->dashboardService->branchTransaction($search));
    }

    public function overallBalances()
    {
        return helper_response($this->dashboardService->overallBalance());
    }

    public function branchesOverallBalances()
    {
        return helper_response($this->dashboardService->branchesOverAllBalance());
    }

    public function branchesOverAllBalancesAt()
    {
        $date = \request()->get('date');

        return helper_response($this->dashboardService->branchesOverAllBalanceAt());
    }

    public function compareAnalytic()
    {
        return helper_response($this->dashboardService->compareAnalytic());
    }

    public function exportTopBalances()
    {
        return $this->dashboardService->exportTopBalances();
    }

    public function profile()
    {
        $user = User::with(['branch','company', 'guarantor'])->find(auth_id());

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
        $user = User::find(auth_id());

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
        $user = User::find(auth_id());

        $path = $request->file('image')->store('users');

        $user->raw_picture = $path;
        $user->save();


        return redirect(route('dashboards.profile'))->with('success_message', 'Profile Changed Successfully');




    }
}
