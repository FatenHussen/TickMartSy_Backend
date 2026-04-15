<?php

namespace App\Http\Controllers\Admin\AffiliateWalletTransaction;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\AffiliateWalletTransaction\FilterRequest;
use App\Services\Admin\AffiliateWalletTransactionService;

class AffiliateWalletTransactionController extends BaseCRUDController
{
    public function __construct(AffiliateWalletTransactionService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
