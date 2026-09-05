<?php

namespace App\Http\Controllers\User\Basket;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CustomBasket\ConfirmRequest;
use App\Http\Requests\User\CustomBasket\StoreItemRequest;
use App\Http\Requests\User\CustomBasket\UpdateItemRequest;
use App\Services\User\CustomBasketService;
use Illuminate\Http\Request;

class CustomBasketController extends Controller
{
    public function __construct(protected CustomBasketService $service)
    {
    }

    public function show($scheduleId)
    {
        return $this->sendResponse(
            $this->service->show((int) $scheduleId),
            'تم جلب السلة المخصصة'
        );
    }

    public function addItem(StoreItemRequest $request, $scheduleId)
    {
        return $this->sendResponse(
            $this->service->addItem((int) $scheduleId, $request->validated()),
            'تمت إضافة المنتج للسلة'
        );
    }

    public function updateItem(UpdateItemRequest $request, $scheduleId, $itemId)
    {
        return $this->sendResponse(
            $this->service->updateItem((int) $scheduleId, (int) $itemId, $request->validated()),
            'تم تحديث الكمية'
        );
    }

    public function removeItem(Request $request, $scheduleId, $itemId)
    {
        return $this->sendResponse(
            $this->service->removeItem((int) $scheduleId, (int) $itemId),
            'تم حذف المنتج من السلة'
        );
    }

    public function confirm(ConfirmRequest $request, $scheduleId)
    {
        $result = $this->service->confirm((int) $scheduleId, $request->validated());

        return $this->sendResponse($result, $result['message']);
    }
}
