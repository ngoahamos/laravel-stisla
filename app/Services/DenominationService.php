<?php

namespace App\Services;

use App\Models\Denomination;

class DenominationService extends AbstractService
{
    protected $model;

    /**
     * DenominationService constructor.
     * @param Denomination $model
     */
    public function __construct(Denomination $model)
    {
        $this->model = $model;
    }

    public function denominations()
    {
        $denominations = $this->model()->orderByDesc('value')->get();
        return prepareResponse(true, $denominations);
    }

    public function denomination($id)
    {
        $denomination = $this->getById($id);
        if ($denomination == null) {
            return $this->notFound("Denomination Not Found");
        }
        return prepareResponse(true, $denomination);
    }

    public function addDenomination(array  $attributes)
    {
        $validData = $this->validate($attributes, ['name' => 'required', 'value' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $denomination = $this->store($attributes);
        if ($denomination == null) {
            $this->storeFailed("Failed to Add Denomination");
        }

        return prepareResponse(true, $denomination);
    }

    public function updateDenomination($id, array  $attributes)
    {
        $denomination = $this->getById($id);

        if ($denomination == null) {
            $this->notFound("Denomination Not Found!");
        }

        $this->update($id, $attributes);

        return prepareResponse(true, $this->getById($id));
    }

}
