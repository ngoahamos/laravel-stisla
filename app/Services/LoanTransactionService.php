<?php

namespace App\Services;

use App\Contracts\LoanTransactionType;
use App\Models\LoanTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoanTransactionService extends AbstractService
{
    protected $model;
    protected $relations = ['loanAccount', 'agent', 'branch', 'loan'];
    /**
     * @var PDFService
     */
    private $PDFService;
    /**
     * @var ActivityService
     */
    private $activityService;
    /**
     * @var LoanBalanceService
     */
    private $balanceService;

    /**
     * LoanTransactionService constructor.
     * @param LoanTransaction $model
     * @param PDFService $PDFService
     * @param ActivityService $activityService
     * @param LoanBalanceService $balanceService
     */
    public function __construct(LoanTransaction $model,
                                PDFService $PDFService,
                                ActivityService $activityService,
                                LoanBalanceService $balanceService)
    {
        $this->model = $model;
        $this->PDFService = $PDFService;
        $this->activityService = $activityService;
        $this->balanceService = $balanceService;
    }

    public function transactions($search)
    {
        $transactions = $this->searchTransactions($search);

        return prepareResponse(true, $transactions);
    }

    public function generateTransactionHistoryPDF($search)
    {
        $transactions = $this->searchTransactions($search);

        $totalCredit = $transactions->where('type', LoanTransactionType::$LOAN)->sum('amount');
        $totalDebit = $transactions->where('type', LoanTransactionType::$REPAYMENT)->sum('amount');

        $_dates = safe_indexing($search, 'dates') ?? [];

        $dates = '';

        if (count($_dates) > 0) {
            $from = $_dates[0] ?? now();
            $to = $_dates[1] ?? now();

            if ($from->copy()->format('j F Y') == $from->copy()->format('j F Y')) {
                $dates = $from->copy()->format('j F Y');
            } else {
                $dates = $from->copy()->format('j F Y') . ' - ' . $to->copy()->format('j F Y');;
            }

        }

        $pdfResponse = $this->PDFService->generatePDF(['transactions' => $transactions,
            'dates' => $dates, 'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit, 'options' => true],
            'pdfs.loan.history');

        if (!$pdfResponse->status)
        {
            return helper_response($pdfResponse);
        }

        $user = Auth::user();
        $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated loan transaction history reported']);

        return $pdfResponse->data->download();


    }

    public function searchTransactions(array $search)
    {
        return $this->model
            ->with($this->relations)
            ->when(safe_indexing($search,'loan_account_id') != null, function($query) use($search) {
                return $query->where('loan_account_id', $search['loan_account_id']);
            })
            ->when(safe_indexing($search,'loan_category_id') != null, function($query) use($search) {
                return $query->where('loan_category_id', $search['loan_category_id']);
            })
            ->when(safe_indexing($search,'branch_id') != null, function($query) use($search) {
                return $query->where('branch_id', $search['branch_id']);
            })
            ->when(safe_indexing($search,'user_id') != null, function($query) use($search) {
                return $query->where('user_id', $search['user_id']);
            })
            ->when(safe_indexing($search, 'type') != null, function ($query) use($search){
                return $query->where('type', $search['type']);
            })
            ->whereBetween('date', $search['dates'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

    }

    public function transaction($id)
    {
        $transaction = $this->getById($id, $this->relations);

        if ($transaction == null)
        {
            return $this->notFound("Transaction Not Found");
        }

        return prepareResponse(true, $transaction);
    }

    public function agentTransactions($user_id, $dates = [])
    {
        $transactions = $this->model->with($this->relations)
            ->where('user_id', $user_id)
            ->whereBetween('date', $dates)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return prepareResponse(true, $transactions);
    }

    public function LoanTransactions($loan_id, $dates = [])
    {
        $transactions = $this->model->with($this->relations)
            ->where('loan_id', $loan_id)
            ->whereBetween('date', $dates)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return prepareResponse(true, $transactions);
    }

    public function addTransaction(array $attributes)
    {
        $validData = $this->validate($attributes,['type' => 'required', 'loan_account_id' => 'required',
            'loan_id' => 'required','loan_category_id' => 'required',
            'company_id' => 'required', 'branch_id' => 'required',
            'amount' => 'required', 'date' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $transactionDate = Carbon::parse($attributes['date']);
        $user = Auth::user();

        if ((now()->format('Y-m-d') != $transactionDate->format("Y-m-d")) && Auth::user()->isAgent()) {
            $this->activityService->addActivity(['company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => "Tried adding Past Loan Transaction "]);

            return prepareResponse(false, ['message' => 'You cannot add Past Transactions.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $loanService = $this->makeLoanService();
        $loan = null;
        if ($loanService != null) {
            $loan = $loanService->findById($attributes['loan_id']);
            if ($loan == null)
            {
                return prepareResponse(false, ["message" => "Loan Not Found."]);
            } else {
                if ($loan->isApproved == false){
                    return prepareResponse(false, ["message" => "Loan Not Approved yet."]);
                }
            }
        } else {
            return prepareResponse(false, ["message" => "Failed to configure Loan Services"]);
        }

        if ($attributes['type'] == LoanTransactionType::$REPAYMENT and
            !$this->balanceService->canRepay($attributes['loan_id'], $attributes['amount']))
        {
            $balance = $this->balanceService->loanBalance($attributes['loan_id']);
            $_amount = !empty($balance) ? $balance->amount: 0;
            $message = "You cannot make Repayment. Current Loan Balance $_amount" ;

            return prepareResponse(false, ['message' => $message], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $transaction = $this->store($attributes);

        if ($transaction == null)
        {
            $this->storeFailed("Failed to Add Transaction");
        }
        $_activity_amount = $attributes['amount'];
        $customerService = $this->makeLoanAccountService();
        $_customer = $customerService ? $customerService->getById($attributes['loan_account_id']) : null;

        if ($attributes['type'] == LoanTransactionType::$LOAN)
        {
            $this->balanceService->grantLoan($attributes['loan_id'], $attributes['amount'], $attributes['loan_account_id'],
                $attributes['company_id'], $attributes['branch_id']);

            $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => "Added Loan of $_activity_amount for " . customer_name($_customer) ]);
        }

        if ($attributes['type'] == LoanTransactionType::$REPAYMENT)
        {
            $this->balanceService->makeRepayment($attributes['loan_id'], $attributes['amount']);


            $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => "Loan Repayment of $_activity_amount for " . customer_name($_customer) ]);
        }

        $balance = $this->balanceService->loanBalance($attributes['loan_id']);
        $transaction->balance = $balance != null ? $balance->amount : 0;
        $transaction->save();
        if ($balance->amount <= 0) {
            $loan->isDefaulted = 0;
            $loan->isPayed = 1;
            $loan->save();
        }
        return $this->transaction($transaction->id);

    }


    public function reverseTransaction($transaction_id)
    {
        $transaction = $this->getById($transaction_id);

        if (empty($transaction))
        {
            return $this->notFound("Transaction Not Found");
        }

        $type = $transaction->type;
        $amount = $transaction->amount;

        $_customer_name = customer_name($transaction->customer);


        $comment = $transaction->comment;
        $auth_name = auth_name();


        if ($type == LoanTransactionType::$LOAN) {
            $des = "Loan Transaction Reversed by $auth_name on " . pretty_date_with_time(now());
            $_activity_des = "Reversed Loan Transactions for $_customer_name on " . pretty_date_with_time(now());
            $comment = strlen($comment) > 0 ? $comment . ". $des" : $des;
            $transaction->comment = $comment;
            $transaction->save();
            $_transaction = $transaction->toArray();
            $_transaction['type'] = LoanTransactionType::$REPAYMENT;
            $_transaction['date'] = now()->format('Y-m-d');
            $this->addTransaction($_transaction);
        } else {
            $des = "Loan Transactions Reversed by $auth_name on " . pretty_date_with_time(now());
            $_activity_des = "Reversed Loan Transactions for $_customer_name on " . pretty_date_with_time(now());
            $comment = strlen($comment) > 0 ? $comment . ". $des" : $des;
            $transaction->comment = $comment;
            $transaction->save();
            $_transaction = $transaction->toArray();
            $_transaction['type'] = LoanTransactionType::$LOAN;
            $_transaction['date'] = now()->format('Y-m-d');
            $this->addTransaction($_transaction);
        }

        $_user = Auth::user();
        $this->activityService->addActivity(['company_id' => $_user->company_id, 'branch_id' => $_user->branch_id,
            'user_id' => auth_id(),
            'action' => $_activity_des ]);

        return $this->transaction($transaction_id);
    }

    public function deleteTransaction($id)
    {
        $transaction = $this->getById($id);

        if (empty($transaction))
        {
            return $this->notFound("Transaction Not Found");
        }
        $auth_name = auth_name();
        $type = $transaction->type;
        $comment = $transaction->comment;

        $des = "Loan Transactions Deleted by $auth_name" ;
        $comment = strlen($comment) > 0 ? $comment . ". $des" : $des;
        $transaction->comment = $comment;
        $transaction->save();
        $_customer_name = customer_name($transaction->customer);
        $_amount = $transaction->amount;


        if ($type == LoanTransactionType::$LOAN)
        {
            $this->balanceService->makeRepayment($transaction->loan_id,$transaction->amount);
        } else {
            $this->balanceService->grantLoan($transaction->loan_id,$transaction->amount, $transaction->loan_account_id,
                $transaction->company_id, $transaction->branch_id);
        }

        $_user = Auth::user();
        $this->activityService->addActivity(['company_id' => $_user->company_id, 'branch_id' => $_user->branch_id,
            'user_id' => auth_id(),
            'action' => "Removed transaction worth [GHS $_amount] from $_customer_name" ]);

        $this->deleteInstance($id);



        return prepareResponse(true, "Transaction Removed");
    }

    public function asAt($type, $date, $user_id = null)
    {
        return $this->model()
            ->when($user_id != null, function ($query) use($user_id) {
                return $query->where('user_id', $user_id);
            })
            ->whereDate('date', $date)
            ->where('type', $type)
            ->sum('amount');
    }

    public function asAtByBranch($type, $date, $branch_id )
    {
        return $this->model()
            ->where('branch_id', $branch_id)
            ->whereDate('date', $date)
            ->where('type', $type)
            ->sum('amount');
    }

    public function runDefaultBatch()
    {
        $balances = $this->balanceService->model()->with(['loan'])->where('amount', '>', 0)->get();

        foreach ($balances as $balance) {

            if ($balance->loan != null && $balance->loan->due_date->isPast())
            {
                $balance->loan->isDefaulted = 1;
                $balance->loan->save();
            }
        }

        return prepareResponse(true, "Operation Completed Successfully");
    }

    public function makeLoanAccountService()
    {
        try {
            return app()->make('App\Services\LoanAccountService');
        } catch (BindingResolutionException $e) {
            return null;
        }
    }

    public function makeLoanService()
    {
        try {
            return app()->make('App\Services\LoanService');
        } catch (BindingResolutionException $e) {
            return null;
        }
    }

    public function analytics()
    {

        $totalLoans = $this->model->where('type', LoanTransactionType::$LOAN)->sum('amount');
        $totalRepayments = $this->model->where('type', LoanTransactionType::$REPAYMENT)->sum('amount');
        return prepareResponse(true, ['loan' =>$totalLoans, 'repayment' => $totalRepayments ]);

    }

    public function destroyLoanBalance($loan_id)
    {
        $this->balanceService->model()->where('loan_id', $loan_id)->delete();

        return prepareResponse(true, "loan balance deleted");
    }


}
