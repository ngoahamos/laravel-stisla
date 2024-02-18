<?php

namespace App\Services;

use App\Contracts\TransactionType;
use App\Exports\TransactionHistoryExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use charlieuki\ReceiptPrinter\ReceiptPrinter as ReceiptPrinter;
use Maatwebsite\Excel\Facades\Excel;

class TransactionService extends AbstractService
{
    protected $model;

    protected $relations = ['customer', 'agent', 'branch'];
    /**
     * @var BalanceService
     */
    private $balanceService;
    /**
     * @var PDFService
     */
    private $PDFService;
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * TransactionService constructor.
     * @param Transaction $model
     * @param BalanceService $balanceService
     * @param PDFService $PDFService
     * @param ActivityService $activityService
     */
    public function __construct(Transaction $model,
                                BalanceService $balanceService,
                                PDFService $PDFService,
                                ActivityService $activityService)
    {
        $this->model = $model;
        $this->balanceService = $balanceService;
        $this->PDFService = $PDFService;
        $this->activityService = $activityService;
    }

    public function transactions($search)
    {
        $transactions = $this->searchTransactions($search);

        return prepareResponse(true, $transactions);
    }

    public function generateTransactionHistoryPDF($search)
    {
        $transactions = $this->searchTransactions($search);

        $totalCredit = $transactions->where('type', TransactionType::$DEPOSIT)->sum('amount');
        $totalDebit = $transactions->where('type', TransactionType::$WITHDRAWAL)->sum('amount');

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
            'pdfs.transactions.history');

        if (!$pdfResponse->status)
        {
            return redirect()->back()->with('error_message', 'Failed to Download PDF');
        }

        $user = Auth::user();
        $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated transaction history reported']);

        return $pdfResponse->data->download();


    }

    public function generateTransactionHistoryExcel($search)
    {
        $transactions = $this->searchTransactions($search);

        $totalCredit = $transactions->where('type', TransactionType::$DEPOSIT)->sum('amount');
        $totalDebit = $transactions->where('type', TransactionType::$WITHDRAWAL)->sum('amount');

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
            'pdfs.transactions.history');

        if (!$pdfResponse->status)
        {
            return redirect()->back()->with('error_message', 'Failed to Download PDF');
        }

        $user = Auth::user();
        $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated transaction history Excel']);

        return Excel::download(new TransactionHistoryExport(['transactions' => $transactions,
            'dates' => $dates, 'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit, 'options' => true]), 'transaction-history.xlsx');


    }

    public function searchTransactions(array $search)
    {
        return $this->model
            ->with($this->relations)
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

    public function customerTransactions($customer_id, $dates = [])
    {
        $transactions = $this->model->with($this->relations)
            ->where('customer_id', $customer_id)
            ->whereBetween('date', $dates)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return prepareResponse(true, $transactions);
    }

    public function addTransaction(array $attributes)
    {
        $validData = $this->validate($attributes,['type' => 'required', 'customer_id' => 'required',
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
                'action' => "Tried adding Past Transaction "]);

            return prepareResponse(false, ['message' => 'You cannot add Past Transactions.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($attributes['type'] == TransactionType::$WITHDRAWAL and
            !$this->balanceService->canWithdraw($attributes['customer_id'], $attributes['amount']))
        {
            $balance = $this->balanceService->customerBalance($attributes['customer_id']);
            $_amount = !empty($balance) ? $balance->amount: 0;
            $message = "You cannot make Withdrawal. Balance is too low. Current Balance $_amount" ;

            return prepareResponse(false, ['message' => $message], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $transaction = $this->store($attributes);

        if ($transaction == null)
        {
            $this->storeFailed("Failed to Add Transaction");
        }
        $_activity_amount = $attributes['amount'];
        $customerService = $this->makeCustomerService();
        $_customer = $customerService ? $customerService->getById($attributes['customer_id']) : null;

        if ($attributes['type'] == TransactionType::$DEPOSIT)
        {
            $this->balanceService->makeDeposit($attributes['customer_id'], $attributes['amount']);

            $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => "Added Deposit of $_activity_amount for " . customer_name($_customer) ]);
        }

        if ($attributes['type'] == TransactionType::$WITHDRAWAL)
        {
            $this->balanceService->makeWithdrawal($attributes['customer_id'], $attributes['amount']);


            $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => "Withdrew $_activity_amount for " . customer_name($_customer) ]);
        }

        $balance = $this->balanceService->customerBalance($attributes['customer_id']);
        $transaction->balance = $balance != null ? $balance->amount : 0;
        $transaction->save();
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
        if ($type == TransactionType::$DEPOSIT) {
            $des = "Transaction Reversed by $auth_name on " . pretty_date_with_time(now());
            $_activity_des = "Reversed Transactions for $_customer_name on " . pretty_date_with_time(now());
            $comment = strlen($comment) > 0 ? $comment . ". $des" : $des;
            $transaction->comment = $comment;
            $transaction->save();
            $_transaction = $transaction->toArray();
            $_transaction['type'] = TransactionType::$WITHDRAWAL;
            $_transaction['date'] = now()->format('Y-m-d');
            $this->addTransaction($_transaction);
        } else {
            $des = "Transactions Reversed by $auth_name on " . pretty_date_with_time(now());
            $_activity_des = "Reversed Transactions for $_customer_name on " . pretty_date_with_time(now());
            $comment = strlen($comment) > 0 ? $comment . ". $des" : $des;
            $transaction->comment = $comment;
            $transaction->save();
            $_transaction = $transaction->toArray();
            $_transaction['type'] = TransactionType::$DEPOSIT;
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

        $des = "Transactions Deleted by $auth_name" ;
        $comment = strlen($comment) > 0 ? $comment . ". $des" : $des;
        $transaction->comment = $comment;
        $transaction->save();
        $_customer_name = customer_name($transaction->customer);
        $_amount = $transaction->amount;


        if ($type == TransactionType::$DEPOSIT)
        {
            $this->balanceService->makeWithdrawal($transaction->customer_id,$transaction->amount);
        } else {
            $this->balanceService->makeDeposit($transaction->customer_id,$transaction->amount);
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

    public function makeCustomerService()
    {
        try {
            return app()->make('App\Services\CustomerService');
        } catch (BindingResolutionException $e) {
            return null;
        }
    }

    public function printerReceipt($transaction_id, $printer)
    {

        $transaction = $this->model->with(['customer'])->find($transaction_id);
        $company = $transaction->customer->company;
        $customer = $transaction->customer;

        // Set params
        $mid = '123123456';
        $store_name = $company->name;
        $store_address = $company->address;
        $store_phone = $company->telephone;
        $store_email = 'info@susugh.com';
        $store_website = 'susugh.com';
        $tax_percentage = 0;

        // Set items
        $items = [
            [
                'name' => $transaction->comment,
                'qty' => 1,
                'price' => $transaction->amount,
            ],
        ];

        // Init printer
        $printer = new ReceiptPrinter;
        $printer->init(
            config('receiptprinter.connector_type'),
            $printer
        );

        // Set store info
        $printer->setStore($mid, $store_name, $store_address, $store_phone, $store_email, $store_website);

        // Add items
        foreach ($items as $item) {
            $printer->addItem(
                $item['name'],
                $item['qty'],
                $item['price']
            );
        }
        // Set tax
//        $printer->setTax($tax_percentage);

        // Calculate total
//        $printer->calculateSubTotal();
//        $printer->calculateGrandTotal();

        // Set transaction ID
        $printer->setTransactionID($transaction_id);
        $printer->setLogo($customer->temp_picture);

        // Set qr code
        $printer->setQRcode([
            'tid' => $transaction_id,
        ]);

        // Print receipt
        $printer->printReceipt();
    }



}
