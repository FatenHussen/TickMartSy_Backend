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

        $user = User::find(1);

        // Get address
        if ($request->filled('address_id')) {
            $address = $user->addresses()->findOrFail($request->address_id);
        } else {
            $address = $user->addresses()
                ->where('is_default', true)
                ->firstOrFail();
        }

        // Variant IDs
        $variantIds = collect($request->items)
            ->unique()
            ->values();

        // All shop IDs
        $shopIds = ShopProductVariant::whereIn('id', $variantIds)
            ->pluck('shop_id')
            ->unique()
            ->values();

        // Paid shops only (exclude free delivery shops)
        $paidShopIds = Shop::whereIn('id', $shopIds)
            ->where('is_free_delivery', false)
            ->pluck('id')
            ->values();

        // If all shops have free delivery
        if ($paidShopIds->isEmpty()) {
            return $this->sendResponse(data: 0);
        }

        // Area IDs for paid shops
        $areaIds = Shop::whereIn('id', $paidShopIds)
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
