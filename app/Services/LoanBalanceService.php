<?php

namespace App\Services;

use App\Models\LoanBalance;

class LoanBalanceService extends AbstractService
{
    protected $model;

    /**
     * LoanBalanceService constructor.
     * @param $model
     */
    public function __construct(LoanBalance $model)
    {
        $this->model = $model;
    }

    public function makeRepayment($loan_id, $amount)
    {
        $balance = $this->loanBalance($loan_id);
        if (!empty($balance))
        {
            $balance->decrement('amount', $amount);

            return true;
        }

        return false;
    }

    public function grantLoan($loan_id, $amount, $loan_account_id, $company_id, $branch_id)
    {
        $balance = $this->loanBalance($loan_id);
        if (!empty($balance))
        {
            $balance->increment('amount', $amount);

            return true;

        } else {
            $balance = $this->store(['loan_id' => $loan_id, 'amount' => $amount,
                'loan_account_id' => $loan_account_id,
                'company_id' => $company_id, 'branch_id' => $branch_id]);

            return $balance != null ? true : false;
        }
    }

    public function loanBalance($loan_id)
    {
        return $this->model->where('loan_id', $loan_id)->first();
    }

    public function canRepay($loan_id, $amount)
    {
        $balance = $this->loanBalance($loan_id);

        if (!empty($balance) and $balance->amount > 0 and ($balance->amount - $amount) >= 0)
        {
            return true;
        }

        return false;
    }

    public function defaultorsAmount($loan_ids)
    {
        if (count($loan_ids) == 0)
        {
            return 0;
        }

        return $this->model->whereIn('loan_id', $loan_ids)->sum('amount');
    }


}
