<?php

namespace App\Services;

use App\Contracts\TransactionType;
use App\Models\BankAccount;

class BankAccountService extends AbstractService
{
    protected $model;

    /**
     * BankAccountService constructor.
     * @param $model
     */
    public function __construct(BankAccount $model)
    {
        $this->model = $model;
    }

    public function bankAccounts()
    {
        $bankAccounts = $this->all();
        return prepareResponse(true, $bankAccounts);
    }

    public function bankAccount($id)
    {
        $bankAccount = $this->getById($id);

        if ($bankAccount == null) {
            return $this->notFound("Bank Account Not Found");
        }
        return prepareResponse(true, $bankAccount);
    }

    public function addBankAccount(array  $attributes)
    {
        $validData = $this->validate($attributes, ['name' => 'required', 'company_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $bankAccount = $this->store($attributes);
        if ($bankAccount == null) {
            $this->storeFailed("Failed to Add Bank Account");
        }

        return prepareResponse(true, $bankAccount);
    }

    public function updateBankAccount($id, array  $attributes)
    {
        $bankAccount = $this->getById($id);

        if ($bankAccount == null) {
            $this->notFound("Bank Account Not Found!");
        }

        $this->update($id, $attributes);

        return prepareResponse(true, $this->getById($id));
    }

    public function addBalance($bank_account_id, $amount, $type)
    {

        if ($amount == null) {
            $amount = 0;
        }
        if ($type == null) {
            $type = 1;
            logger('null reinitialized');
        }



        $bankAccount = $this->getById($bank_account_id);
        if ($bankAccount == null) return false;

        $currentBalance = $bankAccount->balance ?? 0;


        $currentBalance = $type == TransactionType::$DEPOSIT ? $currentBalance + $amount : $currentBalance - $amount;
        $bankAccount->balance = $currentBalance;
        $bankAccount->save();

        return true;
    }
}
