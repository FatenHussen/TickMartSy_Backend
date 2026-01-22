<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\User\SellerRegistration\StoreRequest;
use App\Services\User\SellerRegistrationService;

class SellerRegistrationController extends BaseCRUDController
{
    public function __construct(SellerRegistrationService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
    }
}
