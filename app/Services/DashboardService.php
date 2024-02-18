<?php

namespace App\Services;

use App\Contracts\DateHelperContract;
use App\Contracts\TransactionType;
use App\Exports\ExcelFromViewExport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardService
{
    use DateHelperContract;
    /**
     * @var TransactionService
     */
    private TransactionService $transactionService;
    /**
     * @var BranchService
     */
    private BranchService $branchService;
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var CustomerService
     */
    private CustomerService $customerService;
    /**
     * @var BalanceService
     */
    private BalanceService $balanceService;
    /**
     * @var CompanyService
     */
    private CompanyService $companyService;

    /**
     * DashboardService constructor.
     * @param TransactionService $transactionService
     * @param BranchService $branchService
     * @param UserService $userService
     * @param CustomerService $customerService
     * @param BalanceService $balanceService
     * @param CompanyService $companyService
     */
    public function __construct(TransactionService $transactionService,
                                BranchService $branchService,
                                UserService $userService,
                                CustomerService $customerService,
                                BalanceService $balanceService,
                                CompanyService $companyService)
    {
        $this->transactionService = $transactionService;
        $this->branchService = $branchService;
        $this->userService = $userService;
        $this->customerService = $customerService;
        $this->balanceService = $balanceService;
        $this->companyService = $companyService;
    }

    public function numberAnalytics($user_id = null)
    {
        $accounts = $this->customerService->model()->count();
        $active = $this->customerService->model()->where('status',1)->count();
        $dormant = $this->customerService->model()->where('status',2)->count();
        $closed = $this->customerService->model()->where('status',3)->count();
        $branches = $this->branchService->model()->count();
        $agents = $this->userService->model()->count();
        $male = $this->customerService->model()->where('status', 1)->where('sex','Male')->count();
        $female = $this->customerService->model()->where('status', 1)->where('sex','Female')->count();
        $transactions = $this->transactionService->model()
            ->when($user_id != null, function ($query) use($user_id) { return $query->where('user_id', $user_id); })
            ->whereDate('date', now()->format('Y-m-d'))
            ->count();

        $topDepositors = $this->transactionService->model()
                                                ->with('customer')
                                                ->selectRaw('customer_id, SUM(amount) as total_amount')
                                                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                                                ->where('type', TransactionType::$DEPOSIT)
                                                ->groupBy('customer_id')
                                                ->orderByDesc('total_amount')
                                                ->take(6)
                                                ->get();
        $balances = $this->balanceService->model()
                                        ->with('customer')
                                        ->selectRaw('customer_id, amount')
                                        ->orderByDesc('amount')
                                        ->take(20)
                                        ->get();


       $weekDepositQuery = $this->transactionService->model()
            ->select(DB::raw('DAYNAME(date) as dayOfWeek'), DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('type', TransactionType::$DEPOSIT)
            ->groupBy('dayOfWeek')
            ->get();

        $weekWithdrawalQuery = $this->transactionService->model()
            ->select(DB::raw('DAYNAME(date) as dayOfWeek'), DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('type', TransactionType::$WITHDRAWAL)
            ->groupBy('dayOfWeek')
            ->get();


        $monthDepositQuery = $this->transactionService->model()
            ->select(DB::raw('MONTHNAME(created_at) as month'), DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
            ->where('type', TransactionType::$DEPOSIT)
            ->groupBy('month')
            ->get();
        $monthWithdrawalQuery = $this->transactionService->model()
            ->select(DB::raw('MONTHNAME(created_at) as month'), DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
            ->where('type', TransactionType::$WITHDRAWAL)
            ->groupBy('month')
            ->get();

       $weekDatasets = [];
       $weekDatasets['labels'] = days_of_week();
       $weekDepositData = [];
       $weekWithdrawalData = [];

       foreach ($weekDatasets['labels'] as $label) {
           $value = $weekDepositQuery->where('dayOfWeek',$label)->first();
           $weekDepositData[] = $value != null ? $value->total : 0;

           $dvalue = $weekWithdrawalQuery->where('dayOfWeek',$label)->first();
           $weekWithdrawalData[] = $dvalue != null ? $dvalue->total : 0;
       }

       $weekDatasets['datasets'][0] = [
           'label' => 'Deposit',
           'data' => $weekDepositData,
           'borderWidth' => 5,
           'borderColor' => '#6777ef',
           'backgroundColor' => 'transparent',
           'pointBackgroundColor' => '#fff',
           'pointBorderColor' => '#6777ef',
           'pointRadius' => 4
       ];

        $weekDatasets['datasets'][1] = [
            'label' => 'Withdrawal',
            'data' => $weekWithdrawalData,
            'borderWidth' => 5,
            'borderColor' => '#bf1d89',
            'backgroundColor' => 'transparent',
            'pointBackgroundColor' => '#fff',
            'pointBorderColor' => '#bf1d89',
            'pointRadius' => 4
        ];

        $monthDatasets = [];
        $monthDatasets['labels'] = month_names();
        $monthDepositData = [];
        $monthWithdrawalData = [];

        foreach ($monthDatasets['labels'] as $label) {
            $value = $monthDepositQuery->where('month',$label)->first();
            $monthDepositData[] = $value != null ? $value->total : 0;

            $dvalue = $monthWithdrawalQuery->where('month',$label)->first();
            $monthWithdrawalData[] = $dvalue != null ? $dvalue->total : 0;
        }

        $monthDatasets['datasets'][0] = [
            'label' => 'Deposit',
            'data' => $monthDepositData,
            'borderWidth' => 5,
            'borderColor' => '#6777ef',
            'backgroundColor' => 'transparent',
            'pointBackgroundColor' => '#fff',
            'pointBorderColor' => '#6777ef',
            'pointRadius' => 4
        ];

        $monthDatasets['datasets'][1] = [
            'label' => 'Withdrawal',
            'data' => $monthWithdrawalData,
            'borderWidth' => 5,
            'borderColor' => '#bf1d89',
            'backgroundColor' => 'transparent',
            'pointBackgroundColor' => '#fff',
            'pointBorderColor' => '#bf1d89',
            'pointRadius' => 4
        ];






        return prepareResponse(true, ['accounts' => $accounts, 'active' => $active, 'closed' => $closed, 'dormant' => $dormant, 'branches' => $branches, 'agents' => $agents,
            'num_transactions' => $transactions, 'male' => $male, 'female' => $female, 'topDepositors' => $topDepositors,
            'monthDatasets' => $monthDatasets, 'weekDatasets' => $weekDatasets,
            'topBalances' => $balances]);
    }

    public function transactionAnalytics($user_id = null)
    {
        $periods = CarbonPeriod::create(now()->subDays(8), now()->subDay());

        $depositSeries = [];
        $withdrawalSeries = [];

        $deposit = $this->transactionService
            ->asAt(TransactionType::$DEPOSIT, now()->format('Y-m-d'), $user_id);

        $withdrawal = $this->transactionService
            ->asAt(TransactionType::$WITHDRAWAL, now()->format('Y-m-d'), $user_id);

        foreach ($periods as $period)
        {
            $depositSeries[] = ['x' => $period->format('Y-m-d'),
                'y' => $this->transactionService
                    ->asAt(TransactionType::$DEPOSIT, $period->format('Y-m-d'), $user_id)];

            $withdrawalSeries[] = ['x' => $period->format('Y-m-d'),
                'y' => $this->transactionService
                    ->asAt(TransactionType::$WITHDRAWAL, $period->format('Y-m-d'), $user_id)];
        }

        $balance = $deposit - $withdrawal;

        return prepareResponse(true, ['deposit' => $deposit, 'withdrawal' => $withdrawal, 'balance' => $balance,
            'depositSeries' => $depositSeries, 'withdrawalSeries' => $withdrawalSeries]);
    }

    public function overallBalance()
    {

        $deposit = $this->transactionService->model()->where('type', TransactionType::$DEPOSIT)->sum('amount');

        $withdrawal = $this->transactionService->model()->where('type', TransactionType::$WITHDRAWAL)->sum('amount');

        $balance = $deposit - $withdrawal;

        return prepareResponse(true, ['deposit' => $deposit, 'withdrawal' => $withdrawal, 'balance' => $balance]);

    }

    public function branchesOverAllBalance()
    {
        $branches = $this->branchService->all();
        $balances = [];
        foreach ($branches as $branch) {
            $deposit = $this->transactionService->model()->where([['type', TransactionType::$DEPOSIT],['branch_id', $branch->id] ])->sum('amount');
            $withdrawal = $this->transactionService->model()->where([['type', TransactionType::$WITHDRAWAL],['branch_id', $branch->id] ])->sum('amount');

            $balance = $deposit - $withdrawal;
            $balances[] = ['branch' => $branch->name, 'balance' => $balance, 'deposit' => $deposit,
                'withdrawal' => $withdrawal];
        }

        return prepareResponse(true, $balances);

    }

    public function branchesOverAllBalanceAt($date = null) {
        $date = $date == null ? now() : Carbon::parse($date);

        $branches = $this->branchService->all();
        $balances = [];

        foreach ($branches as $branch) {
            $deposit = $this->transactionService->asAtByBranch(TransactionType::$DEPOSIT, $date, $branch->id);
            $withdrawal = $this->transactionService->asAtByBranch(TransactionType::$WITHDRAWAL, $date, $branch->id);

            $balance = $deposit - $withdrawal;
            $balances[] = ['branch' => $branch->name, 'balance' => $balance, 'deposit' => $deposit,
                'withdrawal' => $withdrawal];
        }
        $_collect = collect($balances);
        $balances[] = ['branch' => 'Total', 'balance' => $_collect->sum('balance'),
                        'deposit' => $_collect->sum('deposit'), 'withdrawal' => $_collect->sum('withdrawal')];

        return prepareResponse(true, $balances);

    }

    public function barTransactions($search = [])
    {
        $ranges = $this->makeIntervals(safe_indexing($search, 'dates'), safe_indexing($search, 'interval'));

        $data = [];
        $branch_id = safe_indexing($search, 'branch_id');
        $title = $this->prettyStartInterval(safe_indexing($search, 'dates'));
        foreach ($ranges as $range) {

            $deposit = $this->transactionService->model()
                ->when($branch_id != null, function ($query) use($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->whereBetween('date', [$range['start'], $range['end']])
                ->where('type', TransactionType::$DEPOSIT)
                ->sum('amount');
            $withdrawal = $this->transactionService->model()
                ->when($branch_id != null, function ($query) use($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->whereBetween('date', [$range['start'], $range['end']])
                ->where('type', TransactionType::$WITHDRAWAL)
                ->sum('amount');

            $_data = [
                'name' => $range['alt_label'],
                'series' => [
                    [
                        'name' => 'Deposit',
                        'value' => $deposit
                    ],
                    [
                        'name' => 'Withdrawal',
                        'value' => $withdrawal
                    ]
                ]
            ];
            array_push($data, $_data);
        }

        return prepareResponse(true, ['data' => $data, 'title' => $title]);
    }

    public function agentTransaction($search)
    {
        $agents = $this->userService->all();
        $total = 0;
        $data = [];

        foreach ($agents as $agent)
        {
            $deposit = $this->transactionService->model()
                ->whereBetween('date', $search['dates'])
                ->where('user_id', $agent->id)
                ->where('type', TransactionType::$DEPOSIT)
                ->sum('amount');
            $total += $deposit;

            array_push($data, ['name' => $agent->name, 'value' => $deposit]);
        }

        return prepareResponse(true, ['data' => $data, 'total' => $total]);
    }

    public function branchTransaction($search)
    {
        $branches = $this->branchService->all();
        $total = 0;
        $data = [];

        foreach ($branches as $branch)
        {
            $deposit = $this->transactionService->model()
                ->whereBetween('date', $search['dates'])
                ->where('branch_id', $branch->id)
                ->where('type', TransactionType::$DEPOSIT)
                ->sum('amount');
            $total += $deposit;

            $data[] = ['name' => $branch->name, 'value' => $deposit];
        }

        return prepareResponse(true, ['data' => $data, 'total' => $total]);
    }

    public function compareAnalytic()
    {
        $currentDate = Carbon::now();
        $yesterdayStart = Carbon::yesterday();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();

        $yesterdayQuery = $this->transactionService->model()
                    ->select('amount', 'type')
                    ->whereDate('date', $yesterdayStart)
                    ->get();

        $todayQuery = $this->transactionService->model()
                    ->select('amount', 'type')
                    ->whereDate('date',$currentDate)
                    ->get();
        $lastMonthQuery = $this->transactionService->model()
                    ->select('amount', 'type')
                    ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
                    ->get();
        $monthQuery = $this->transactionService->model()
                    ->select('amount', 'type')
                    ->whereBetween('date', [$thisMonthStart, $thisMonthEnd])
                    ->get();

        $todayDeposit = $todayQuery->where('type', TransactionType::$DEPOSIT)->sum('amount');
        $todayWithdrawal = $todayQuery->where('type', TransactionType::$WITHDRAWAL)->sum('amount');

        $yesterdayDeposit = $yesterdayQuery->where('type', TransactionType::$DEPOSIT)->sum('amount');
        $yesterdayWithdrawal = $yesterdayQuery->where('type', TransactionType::$WITHDRAWAL)->sum('amount');


        $monthDeposit = $monthQuery->where('type', TransactionType::$DEPOSIT)->sum('amount');

        $monthWithdrawal = $monthQuery->where('type', TransactionType::$WITHDRAWAL)->sum('amount');

        $lastMonthDeposit = $lastMonthQuery->where('type', TransactionType::$DEPOSIT)->sum('amount');
        $lastMonthWithdrawal = $lastMonthQuery->where('type', TransactionType::$WITHDRAWAL)->sum('amount');

        $data =  [
            'todayDeposit' => $todayDeposit,
            'todayDepositDif' => percentage_calc($todayDeposit, $yesterdayDeposit),
            'todayWithdrawal' => $todayWithdrawal,
            'todayWithdrawalDif' => percentage_calc($todayWithdrawal, $yesterdayWithdrawal),
            'thisMonthDeposit' => $monthDeposit,
            'thisMonthDepositDif' => percentage_calc($monthDeposit, $lastMonthDeposit),
            'thisMonthWithdrawal' => $monthWithdrawal,
            'thisMonthWithdrawalDif' => percentage_calc($monthWithdrawal, $lastMonthWithdrawal)

        ];

        return prepareResponse(true, $data);

    }

    public function exportTopBalances()
    {
        $balances = $this->balanceService->model()->with('customer')
            ->selectRaw('customer_id, amount')
            ->orderByDesc('amount')
            ->take(20)
            ->get();

        return Excel::download(new ExcelFromViewExport('exports.balances.top-balance', ['balances'=>$balances]), 'top-balances.xlsx');
    }
}
