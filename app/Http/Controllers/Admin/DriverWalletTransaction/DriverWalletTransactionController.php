<?php

namespace App\Http\Controllers\Admin\DriverWalletTransaction;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\DriverWalletTransaction\FilterRequest;
use App\Services\Admin\DriverWalletTransactionService;

class DriverWalletTransactionController extends BaseCRUDController
{
    public function __construct(DriverWalletTransactionService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
