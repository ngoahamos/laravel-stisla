<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Customer;
use Exception;

class BalanceService extends AbstractService
{
    protected $model;

    /**
     * BalanceService constructor.
     * @param $model
     */
    public function __construct(Balance $model)
    {
        $this->model = $model;
    }

    public function makeWithdrawal($customer_id, $amount)
    {
        $balance = $this->customerBalance($customer_id);
        if (!empty($balance))
        {
            $balance->decrement('amount', $amount);

            return true;
        }

        return false;
    }

    public function makeDeposit($customer_id, $amount)
    {
        $balance = $this->customerBalance($customer_id);
        if (!empty($balance))
        {
            $balance->increment('amount', $amount);

            return true;

        } else {
            // Fix this later
            $customer = Customer::find($customer_id);
            $balance = $this->store([
                'customer_id' => $customer_id,
                'amount' => $amount,
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id]);


            return $balance != null ? true : false;
        }
    }

    public function customerBalance($customer_id)
    {
        return $this->model->where('customer_id', $customer_id)->first();
    }

    public function canWithdraw($customer_id, $amount)
    {
        $balance = $this->customerBalance($customer_id);

        if (!empty($balance) and $balance->amount >= $amount)
        {
            return true;
        }

        return false;
    }

    public function updateBranch($customer_id, $new_branch_id): bool
    {
        try {
            $balance = $this->model->where('customer_id', $customer_id)->first();
            if ($balance != null) {
                $balance->branch_id = $new_branch_id;
                $balance->save();

                return true;
            }
        }catch (Exception $exception) {
            logger("failed to update $customer_id branch to $new_branch_id");
        }

        return false;
    }


}
