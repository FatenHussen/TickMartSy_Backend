<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Services\User\PaymentMethodService;

class PaymentMethodController extends BaseIndexController
{
    public function __construct(PaymentMethodService $service)
    {
        $this->service = $service;
    }
}
