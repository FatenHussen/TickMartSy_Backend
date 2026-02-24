<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Faq\UpdateRequest;
use App\Http\Requests\Admin\Faq\StoreRequest;
use App\Services\Admin\FaqService;

class FaqController extends BaseCRUDController
{
    public function __construct(
        FaqService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
