<?php

namespace App\Services;

use App\Contracts\TransactionType;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerService extends AbstractService
{
    protected $model;

    protected $relations = ['address', 'nextOfKin', 'branch', 'agent', 'region', 'IDType', 'product', 'balance'];
    /**
     * @var AddressService
     */
    private $addressService;
    /**
     * @var NextOfKinService
     */
    private $nextOfKinService;
    /**
     * @var PDFService
     */
    private $PDFService;
    /**
     * @var BranchService
     */
    private $branchService;
    /**
     * @var TransactionService
     */
    private $transactionService;
    /**
     * @var ActivityService
     */
    private $activityService;
    private BalanceService $balanceService;

    /**
     * CustomerService constructor.
     * @param Customer $model
     * @param AddressService $addressService
     * @param NextOfKinService $nextOfKinService
     * @param PDFService $PDFService
     * @param BranchService $branchService
     * @param TransactionService $transactionService
     * @param ActivityService $activityService
     * @param BalanceService $balanceService
     */
    public function __construct(Customer $model,
                                AddressService $addressService,
                                NextOfKinService $nextOfKinService,
                                PDFService $PDFService,
                                BranchService $branchService,
                                TransactionService $transactionService,
                                ActivityService $activityService,
                                BalanceService $balanceService)
    {
        $this->model = $model;
        $this->addressService = $addressService;
        $this->nextOfKinService = $nextOfKinService;
        $this->PDFService = $PDFService;
        $this->branchService = $branchService;
        $this->transactionService = $transactionService;
        $this->activityService = $activityService;
        $this->balanceService = $balanceService;
    }

    public function customers($status =1)
    {
        $customers = $this->model->with($this->relations)
            ->orderBy('surname')
            ->when($status > -1, function ($query)use($status){
                return $query->where('status', $status);
            })
            ->orderBy('status')
            ->get();

        return prepareResponse(true, $customers);
    }

    public function customer($id)
    {
        $customer = $this->getById($id, $this->relations);

        return $customer == null ? $this->notFound('Customer Not Found') : prepareResponse(true, $customer);
    }

    public function addCustomer(array $attributes = [])
    {

        $validData = $this->validate($attributes,['surname' => 'required', 'other_names' => 'required',
            'company_id' => 'required', 'branch_id' => 'required',
            'product_id' => 'required', 'sex' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        if (empty(safe_indexing($attributes, 'account_number') ) or
            $this->accountExists($attributes['account_number']))
        {
            $count = 0;
            do {
                $account_number = $this->generateAccountNumber(safe_indexing($attributes, 'branch_id'), $count);
                $attributes['account_number'] = $account_number;
                $count += 1;
            } while($this->accountExists($account_number));
        }

        $customer =  $this->store($attributes);

        if ($customer == null)
        {
            return $this->storeFailed("Failed to Add Customer");
        }

        $attributes['customer_id'] = $customer->id;

        $this->updateAddress($customer->id, $attributes);

        $this->updateNextOfKin($customer->id, $attributes);

        $this->activityService->addActivity(['company_id' => $customer->company_id, 'branch_id' => $customer->branch_id,
            'user_id' => auth_id(),
            'action' => 'Added new Account [' . customer_name($customer) . ']']);


        return $this->customer($customer->id);
    }

    public function updateCustomer($id, array $attributes)
    {
        $customer = $this->getById($id);

        if ($customer == null)
        {
            return $this->notFound("Customer Not Found");
        }
        $account_number = safe_indexing($attributes, 'account_number');
        $branch_id = $customer->branch_id;
        if (!empty($account_number) and $customer->account_number != $account_number)
        {
            $count = 0;
            while ($this->accountExists($account_number)) {
                $account_number = $this->generateAccountNumber($customer->branch_id, $count);
                $count += 1;
            }

            $attributes['account_number'] = $account_number;
        }
        $_incoming_branch_id =  safe_indexing($attributes, 'branch_id');
        if ($_incoming_branch_id != null) {
            if ($branch_id != $_incoming_branch_id) {
                $this->transactionService->model()->where('customer_id', $id)->update(['branch_id' => $_incoming_branch_id]);
                $this->balanceService->updateBranch($id, $_incoming_branch_id);
            }
        }


        $this->update($id, $attributes);

        $this->updateAddress($id, $attributes);

        $this->updateNextOfKin($id, $attributes);

        $this->activityService->addActivity(['company_id' => $customer->company_id, 'branch_id' => $customer->branch_id,
            'user_id' => auth_id(),
            'action' => 'Updated Account [' . customer_name($customer) . '] details']);

        return $this->customer($id);
    }

    public function updateAddress($customer_id, $attributes)
    {
        $this->addressService->addAddress($customer_id, $attributes);
    }

    public function updateNextOfKin($customer_id, $attributes)
    {
        if (safe_indexing($attributes,'kin_name') != null)
        {
            $this->nextOfKinService->addNextOfKin($customer_id, [
                'name' => safe_indexing($attributes, 'kin_name'),
                'contact' => safe_indexing($attributes, 'kin_contact'),
                'relationship' => safe_indexing($attributes, 'kin_relationship'),
                'mandate' => safe_indexing($attributes, 'kin_mandate')
            ]);
        }

    }

    public function changeAccountStatus($customer_id, $status) {
        $customer = $this->getById($customer_id);

        if ($customer == null) return $this->notFound("Customer Not Found");

        $customer->status = $status;

        $customer->save();

        $this->activityService->addActivity(['company_id' => auth_company_id(), 'branch_id' => auth_branch_id(),
            'user_id' => auth_id(),
            'action' => 'Changed customer status [' . $customer_id . '] details']);

        return $this->customer($customer_id);
    }

    public function generateAccountNumber($branch_id = null, $count = 0)
    {

        $branch = !empty($branch_id) ? $this->branchService->getById($branch_id) : null;
        $suffix =  $this->accountNumberSuffix();
        $prefix =  (!empty($branch) and !empty($branch->code)) ? $branch->code : date('Ymd');

        if (count(func_get_args()) == 2) {
            if ($count > 0)
            {
                $suffix = pad_zeros($this->accountNumberSuffix(false) + $count);
            }
        }

        return $prefix . $suffix;
    }

    public function accountExists($account_number)
    {
        return $this->model->where('account_number', $account_number)->first() != null;
    }

    public function accountNumberSuffix($pad = true)
    {
        $num =  DB::table('customers')
            ->where('company_id', Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->count();

        return $pad ? pad_zeros($num+1) : $num+1;
    }

    public function lastAccountNumber()
    {
        $customer = $this->model->latest()->first();

        return prepareResponse(true, $customer == null ?
            $this->generateAccountNumber() : $customer->account_number);
    }

    public function addPicture(Request $request)
    {
        $validData = $this->validate($request->toArray(), ['file' => 'required|file', 'customer_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $path = $request->file('file')->store('customers');

        $customer = $this->getById($request->get('customer_id'));
        if ($customer) {
            $customer->raw_picture = $path;
            $customer->save();

            $this->activityService->addActivity(['company_id' => $customer->company_id, 'branch_id' => $customer->branch_id,
                'user_id' => auth_id(),
                'action' => 'Uploaded Picture for [' . customer_name($customer) . ']']);

            return prepareResponse(true, "Picture Saved");
        }

        return prepareResponse(false, ["message" => "Failed to Save Picture"],
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function addSignature(Request $request)
    {
        $validData = $this->validate($request->toArray(), ['signature' => 'required|file', 'customer_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $path = $request->file('signature')->store('signatures');

        $customer = $this->getById($request->get('customer_id'));
        if ($customer) {
            $customer->raw_signature = $path;
            $customer->save();

            $this->activityService->addActivity(['company_id' => $customer->company_id, 'branch_id' => $customer->branch_id,
                'user_id' => auth_id(),
                'action' => 'Uploaded Signature for [' . customer_name($customer) . ']']);

            return prepareResponse(true, "Picture Saved");
        }

        return prepareResponse(false, ["message" => "Failed to Save Picture"],
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function accountBalances()
    {
        $accounts = $this->all();
        $total = $accounts->sum('balance.amount');
        $pdfResponse = $this->PDFService->generatePDF(['accounts' => $accounts, 'total' => $total],
            'pdfs.accounts.account-balance');

        if (!$pdfResponse->status)
        {
            return helper_response($pdfResponse);
        }

        $user = Auth::user();

        $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated account balances reported']);

        return $pdfResponse->data->download();
    }

    public function statement($customer_id, $dates)
    {
        $customer = $this->getById($customer_id);
        if ($customer == null)
        {
            throw  new \Exception("Account Not Found");
        }
        $transactions = $this->transactionService->model()
            ->where('customer_id', $customer_id)
            ->whereBetween('date', $dates)
            ->orderBy('date')
            ->get();

        $debt = $transactions->where('type', TransactionType::$WITHDRAWAL)->sum('amount');
        $credit = $transactions->where('type', TransactionType::$DEPOSIT)->sum('amount');

        $balance = $customer->balance ? $customer->balance->amount : 0;

        $from = $dates[0] ?? now();
        $to = $dates[1] ?? now();


        $pdfResponse = $this->PDFService->generatePDF(['transactions' => $transactions,
            'customer' => $customer,
            'debt' => $debt,
            'credit' => $credit,
            'from' => $from,
            'to' => $to,
            'options' => 'true',
            'balance' => $balance], 'pdfs.accounts.statement');
        if (!$pdfResponse->status) {
            return helper_response($pdfResponse);
        }

        $this->activityService->addActivity(['company_id' => $customer->company_id, 'branch_id' => $customer->branch_id,
            'user_id' => auth_id(),
            'action' => 'Generated account statement for ['. customer_name($customer) . ']']);

        return $pdfResponse->data->download();
    }

    public function uploadAccounts(array $attributes)
    {

        $accounts = safe_indexing($attributes, 'accounts');
        $accounts = $accounts == null ? [] : $accounts;

        $productService = app()->make('App\Services\ProductService');

        $noBranch = 0;
        $failed = 0;
        $success = 0;
        $company_id = safe_indexing($attributes, 'company_id');
        $uploadedAccounts = [];
        foreach ($accounts as $account) {
            $branch = $this->branchService->model()->where('name', $account['branch'])->first();
            if ($branch == null) {
                $noBranch += 1;
                $account['uploaded'] = false;
                $account['upload_message'] = 'Branch does not exists';
                array_push($uploadedAccounts, $account);
                continue;
            }

            $account['branch_id'] = $branch->id;
            $_product = safe_indexing($account, 'product');
            $product = $_product == null ? null: $productService->model()->where('name', $_product)->first();
            if ($product != null)
            {
                $account['product_id'] = $product->id;
            }

            $account['company_id'] = $company_id;
            $account_number  = safe_indexing($attributes, 'account_number');
            $accountExits = $account_number == null ? false: $this->accountExists($account_number);
            if ($accountExits) {

                $_customer = $this->find(['account_number' => safe_indexing($attributes, 'account_number')], true);
                $response = $this->updateCustomer($_customer->id, $account);
            } else {
                $passbook = safe_indexing($account, 'passbook_number');

                $_customer = $passbook =! null ? $this->find(['passbook_number' => $passbook], true) : null;

                if ($_customer != null)
                {
                    $accountExits = true;
                    $response = $this->updateCustomer($_customer->id, $account);
                } else {

                    $response = $this->addCustomer($account);
                }

            }


            if (!$response->status) {
                $failed += 1;
                $account['uploaded'] = false;
                $account['upload_message'] = $response->errors['message'] ?? '';
                array_push($uploadedAccounts, $account);
            } else {
                $account['uploaded'] = true;


                $success += 1;
                if (!$accountExits)
                {
                    $balance = safe_indexing($account, 'balance');
                    $balance = $balance == null ? 0 : $balance;
                    $this->transactionService->addTransaction([
                        'type' => TransactionType::$DEPOSIT,
                        'amount' => $balance,
                        'company_id' => $company_id,
                        'branch_id' => $branch->id,
                        'date' => now()->format('Y-m-d'),
                        'comment' => 'Balance as at 30th June 2020',
                        'customer_id' => $response->data->id,
                        'user_id' => auth_id()

                    ]);
                    $account['upload_message'] = 'Upload Successful';
                } else {
                    $account['upload_message'] = 'Account Updated';
                }
                array_push($uploadedAccounts, $account);

            }

        }

        $successMessage = '';
        $failedMessage = '';
        $noBranchMessage = '';
        if ($success > 0) {
            $plural = $success > 1 ? 'Accounts' : 'Account';
            $successMessage .= "$success $plural Added Successfully. ";
        }

        if ($failed > 0) {
            $plural = $failed > 1 ? 'Accounts' : 'Account';
            $failedMessage = "$failed $plural Not Saved. ";
        }

        if ($noBranch > 0) {
            $plural = $noBranch > 1 ? 'Accounts' : 'Account';
            $noBranchMessage = "$noBranch $plural Not Saved, because Branch provided does not Exits. ";
        }

        $messages = [['message' => $successMessage, 'color' => 'green'], ['message' => $failedMessage, 'color' => 'red'],
            ['message' =>$noBranchMessage, 'color' => 'volcano']];
        return prepareResponse(true, ['accounts' => $uploadedAccounts, 'messages' => $messages]);
    }



}
