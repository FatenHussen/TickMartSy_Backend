<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\LegalDocument\FilterRequest;
use App\Http\Requests\Admin\LegalDocument\StoreRequest;
use App\Http\Requests\Admin\LegalDocument\UpdateRequest;
use App\Services\Admin\LegalDocumentService;

class LegalDocumentController extends BaseCRUDController
{
    public function __construct(
        LegalDocumentService $service
    ) {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
