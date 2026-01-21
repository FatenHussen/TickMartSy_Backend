<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Cart\StoreRequest;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Services\Base\DeliveryPricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // public function __construct(protected DeliveryPricingService $service)
    // {
    //     $this->service = $service;
    // }

    public function calculateDeliveryPrice(StoreRequest $request)
    {
        /** @var User $user */
        $user = auth('user')->user();

        $user = User::findOrFail(1);

        if ($request->filled('address_id')) {
            $address = $user->addresses()->findOrFail($request->address_id);
        } else {
            $address = $user->addresses()
                ->where('is_default', true)
                ->firstOrFail();
        }


        $variantIds = collect($request->items)
            ->unique()
            ->values();

        $shopIds = ShopProductVariant::whereIn('id', $variantIds)
            ->pluck('shop_id')
            ->unique()
            ->values();


        $areaIds = Shop::whereIn('id', $shopIds)
            ->pluck('area_id')
            ->unique()
            ->values()
            ->toArray();


        $price = DeliveryPricingService::calculateDeliveryFee(
            $address->area_id,
            $areaIds
        );
        return $this->sendResponse(data: $price);
    }
}
