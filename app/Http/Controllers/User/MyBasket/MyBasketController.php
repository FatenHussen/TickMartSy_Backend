<?php

namespace App\Http\Controllers\User\MyBasket;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\MyBasket\FilterRequest;
use App\Services\User\MyBasketService;

class MyBasketController extends Controller
{
    public function __construct(protected MyBasketService $service)
    {
    }

    public function index(FilterRequest $request)
    {
        $userId = auth('user')->id();
        $type = $request->input('type', 'all');
        $data = $this->service->getMyBaskets($userId, $type);
        return $this->sendResponse(data: $data);
    }
    public function pauseSubscription($basketId)
    {
        $userId = auth('user')->id();

        $this->service->pauseSubscriptionBasket($userId, $basketId);

        return response()->json([
            'message' => 'Basket paused successfully'
        ]);
    }
    public function resumeSubscription($basketId)
    {
        $userId = auth('user')->id();

        $this->service->resumeSubscriptionBasket($userId, $basketId);

        return response()->json([
            'message' => 'Basket resumed successfully'
        ]);
    }
}
