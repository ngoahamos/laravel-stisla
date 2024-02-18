<?php

namespace App\Services;

use App\Models\NextOfKin;

class NextOfKinService extends AbstractService
{
    protected $model;

    /**
     * NextOfKinService constructor.
     * @param $model
     */
    public function __construct(NextOfKin $model)
    {
        $this->model = $model;
    }

    public function addNextOfKin($customer_id, $attributes = [])
    {
        if (count($attributes) > 0) {
            $this->model->updateOrCreate(['customer_id' => $customer_id], $attributes);
        }
    }


}
