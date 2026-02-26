<?php

namespace App\Http\Controllers\Admin\SellerRegistration;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\SellerRegistration\ApproveRequest;
use App\Services\Admin\SellerRegistrationService;
use Illuminate\Http\Request;

class SellerRegistrationCrudController extends BaseCRUDController
{
    public function __construct(
        SellerRegistrationService $service
    ) {
        $this->service = $service;
    }

    /**
     * Approve seller registration
     */
    public function approve($id, ApproveRequest $request)
    {

        $result = $this->service->approve($id, $request->validated());

        return $this->sendResponse($result);
    }

    /**
     * Reject seller registration
     */
    public function reject($id)
    {
        $this->service->reject($id);
        return $this->sendResponse();
    }
}
