<?php

namespace App\Http\Controllers\Admin\Store;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Store\StoreRequest;
use App\Http\Requests\Admin\Store\UpdateRequest;
use App\Http\Resources\Store\AllResource;
use App\Http\Resources\Store\OneResource;
use App\Models\Store;
use App\Services\Admin\StoreService;

class StoreCrudController extends BaseCRUDController
{
    public function __construct(
        protected StoreService $service
    ) {
        // $this->filterRequest = AdsFilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }


}
