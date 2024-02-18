<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SMSBalance;

class SMSBalanceService extends AbstractService
{
    protected $model;
    private ActivityService $activityService;

    /**
     * @param SMSBalance $model
     * @param ActivityService $activityService
     */
    public function __construct(SMSBalance $model, ActivityService $activityService)
    {
        $this->model = $model;
        $this->activityService = $activityService;
    }

    public function companyBalance($company_id)
    {
        $balance = $this->model->where('company_id', $company_id)->first();

        if ($balance) return prepareResponse(true, $balance);

        return prepareResponse(false, ['message' => 'Not Data Found']);
    }

    public function storeBalance($company_id, $sender_id)
    {
        $pre = $this->model->where('company_id', $company_id)->first();
        if ($pre) return prepareResponse(true, $pre);

        $balance = $this->model->create([
            'company_id' => $company_id,
            'sender_id' => $sender_id,
            'balance' => 10,
            'user_id' => auth_id()
        ]);

        return prepareResponse( (bool)$balance, $balance ? $balance : ['message' => 'Failed to create sms balance']);

    }

    public function reduceBalance($company_id, $cost)
    {
        $balance = $this->model->where('company_id', $company_id)->first();

        if (!$balance) return true;
        $balance->balance = $balance->balance - $cost;

        $balance->save();

         return true;

    }

    public function hasEnoughBalance($company_id, $num=1): bool
    {
        // admin sending ...
        if ($company_id == null) return true;

        $balance = $this->model->where('company_id', $company_id)->first();
        if ($balance != null && $balance->balance >= $num) return  true;

        return false;
    }

    public function addBalance($company_id, $amount)
    {
        $balance = $this->model->where('company_id', $company_id)->first();

        if (!$balance) {
            $company = Company::find($company_id);
            if ($company == null) return false;

            $balance = $this->model()->create([
                'balance' => $amount,
                'company_id' => $company_id,
                'sender_id' => $company->sms_sender_id,
                'user_id' => auth_id()]);
        } else {
            $balance->balance = $balance->balance + $amount;
            $balance->save();
        }
        ;

        $this->activityService->addActivity(['company_id' => auth_company_id() ,
            'branch_id' => auth_branch_id(),
            'action',
            'user_id' => auth_id(),
            'subject_id' => $balance->id,
            'subject_type' => 'sms_balances']);

        return true;
    }


}
