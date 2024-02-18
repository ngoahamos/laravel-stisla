<?php

namespace App\Services;

use App\Models\Guarantor;

class GuarantorService extends AbstractService
{
    protected $model;

    /**
     * GuarantorService constructor.
     * @param $model
     */
    public function __construct(Guarantor $model)
    {
        $this->model = $model;
    }

    public function addGuarantor(array $attributes)
    {
        $this->model->updateOrCreate(['user_id' => $attributes['user_id']], $attributes);
    }
}
