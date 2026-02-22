<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\LegalDocument\UpdateRequest;
use App\Services\Admin\LegalDocumentService;

class LegalDocumentController extends BaseCRUDController
{
    public function __construct(
        LegalDocumentService $service
    ) {
        $this->service = $service;
        $this->createRequest = UpdateRequest::class;
    }
}
