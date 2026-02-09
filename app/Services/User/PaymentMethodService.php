<?php

namespace App\Services\User;

use App\Http\Resources\User\PaymentMethod\AllResource;
use App\Models\PaymentMethod;
use App\Services\BaseService;

class PaymentMethodService extends BaseService
{
    public function __construct(PaymentMethod $model)
    {
        $this->model      = $model;
        $this->collection = AllResource::class;
        $this->relations  = [];
        $this->pagination = true;
    }

}
