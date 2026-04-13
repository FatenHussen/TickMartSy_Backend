<?php

namespace App\Http\Controllers\Admin\UserBasketSchedule;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\Admin\UserBasketSchedule\FilterRequest;
use App\Services\Admin\UserBasketScheduleService;

class UserBasketScheduleController extends BaseIndexController
{
    protected $service;

    public function __construct(UserBasketScheduleService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }

    public function disable($id)
    {
        $result = $this->service->disable((int) $id);

        return $this->sendResponse($result, 'تم تعطيل السلة المجدولة بنجاح');
    }

    public function enable($id)
    {
        $result = $this->service->enable((int) $id);

        return $this->sendResponse($result, 'تم تفعيل السلة المجدولة بنجاح');
    }



}
