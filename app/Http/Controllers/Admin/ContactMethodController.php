<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ContactMethod\StoreRequest;
use App\Http\Requests\Admin\ContactMethod\UpdateRequest;
use App\Services\Admin\ContactMethodService;

class ContactMethodController extends BaseCRUDController
{
    public function __construct(ContactMethodService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
