<?php

namespace App\Services;

use App\Models\Address;

class AddressService extends AbstractService
{
    protected $model;

    /**
     * AddressService constructor.
     * @param $model
     */
    public function __construct(Address $model)
    {
        $this->model = $model;
    }

    public function addAddress($customer_id, $attributes = [])
    {
        if (count($attributes) > 0) {
            $this->model->updateOrCreate(['customer_id' => $customer_id], $attributes);
        }
    }

}
