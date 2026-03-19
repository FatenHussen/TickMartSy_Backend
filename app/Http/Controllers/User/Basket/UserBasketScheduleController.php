<?php

namespace App\Http\Controllers\User\Basket;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\BasketSchedule\StoreRequest;
use App\Http\Requests\User\BasketSchedule\UpdateRequest;
use App\Services\User\UserBasketScheduleService;
use Illuminate\Http\Request;

class UserBasketScheduleController extends BaseCRUDController
{
    public function __construct(UserBasketScheduleService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    /**
     * Pause a scheduled basket
     */
    public function pause(Request $request, $id)
    {
        try {
            $result = $this->service->pause($id);
            return $this->sendResponse($result, 'تم إيقاف السلة المجدولة بنجاح');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    /**
     * Resume a paused scheduled basket
     */
    public function resume(Request $request, $id)
    {
        try {
            $result = $this->service->resume($id);
            return $this->sendResponse($result, 'تم استئناف السلة المجدولة بنجاح');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }
}
