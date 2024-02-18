<?php

namespace App\Services;

use App\Models\DailyClosingStatedAs;

class DailyClosingStatedAsService extends AbstractService
{
    protected $model;

    protected $relations = ['bankAccount'];

    /**
     * DailyClosingStatedAsService constructor.
     * @param DailyClosingStatedAs $model
     */
    public function __construct(DailyClosingStatedAs $model)
    {
        $this->model = $model;
    }

    public function addStatedAs(array $attributes)
    {

        $validData = $this->validate($attributes, ['daily_closing_id' => 'required', 'bank_account_id' => 'required']);


        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $statedAs = $this->store($attributes);
        if ($statedAs == null) {
            $this->storeFailed("Failed to Add Data");
        }

        return prepareResponse(true, $statedAs);
    }
}
