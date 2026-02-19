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
}
