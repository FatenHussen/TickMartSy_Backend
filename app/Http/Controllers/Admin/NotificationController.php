<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\AdminNotification\StoreRequest;
use App\Http\Requests\Admin\AdminNotification\FilterRequest;

use App\Services\Admin\AdminNotificationService;

class NotificationController extends BaseCRUDController
{
    public function __construct(
        AdminNotificationService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
