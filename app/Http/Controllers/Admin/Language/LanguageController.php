<?php

namespace App\Http\Controllers\Admin\Language;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Language\StoreRequest;
use App\Http\Requests\Admin\Language\FilterRequest;
use App\Http\Requests\Admin\Language\UpdateRequest;
use App\Services\Admin\LanguageService;

class LanguageController extends BaseCRUDController
{
    public function __construct(LanguageService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
