<?php

namespace App\Services;

use App\Models\DailyClosingAgent;

class DailyClosingAgentService extends AbstractService
{
    protected $model;

    protected $relations = ['agent'];

    /**
     * DailyClosingAgentService constructor.
     * @param DailyClosingAgent $model
     */
    public function __construct(DailyClosingAgent $model)
    {
        $this->model = $model;
    }

    public function addDailyAgent(array $attributes)
    {
        $validData = $this->validate($attributes, ['daily_closing_id' => 'required', 'user_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $agentData = $this->store($attributes);
        if ($agentData == null) {
            $this->storeFailed("Failed to Add Data");
        }

        return prepareResponse(true, $agentData);
    }
}
