<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Cart\StoreRequest;

use App\Models\User;
use App\Services\User\CalculateDeliveryPriceService;

class CartController extends Controller
{
    public function calculateDeliveryPrice(
        StoreRequest $request,
    ) {
        /** @var User $user */
        $user = auth('user')->user();

        $price = CalculateDeliveryPriceService::handle(
            user: $user,
            items: $request->items,
            addressId: $request->address_id
        );

        return $this->sendResponse(data: $price);
    }
}
