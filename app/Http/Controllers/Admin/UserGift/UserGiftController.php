<?php

namespace App\Http\Controllers\Admin\UserGift;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\UserGift\StoreRequest;
use App\Http\Requests\Admin\UserGift\UpdateRequest;
use App\Services\Admin\UserGiftService;

class UserGiftController extends BaseCRUDController
{
    public function __construct(UserGiftService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
