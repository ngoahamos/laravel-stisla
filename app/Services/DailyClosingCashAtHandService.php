<?php

namespace App\Services;

use App\Models\DailyClosingCashAtHand;

class DailyClosingCashAtHandService extends AbstractService
{
    protected $model;

    protected $relations = ['denomination'];

    /**
     * DailyClosingCashAtHandService constructor.
     * @param DailyClosingCashAtHand $model
     */
    public function __construct(DailyClosingCashAtHand $model)
    {
        $this->model = $model;
    }

    public function addCashAtHand(array $attributes)
    {

        $validData = $this->validate($attributes, ['daily_closing_id' => 'required', 'denomination_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $cashAtHand = $this->store($attributes);
        if ($cashAtHand == null) {
            $this->storeFailed("Failed to Add Data");
        }

        return prepareResponse(true, $cashAtHand);
    }

}
