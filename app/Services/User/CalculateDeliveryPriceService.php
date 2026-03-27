<?php

namespace App\Services\User;

use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Services\Base\DeliveryPricingService;
use Illuminate\Support\Facades\Log;

class CalculateDeliveryPriceService
{
    public static function handle(User $user, array $items, ?int $addressId = null): float
    {
        // Get address
        if ($addressId) {
            $address = $user->addresses()->findOrFail($addressId);
        } else {
            $address = $user->addresses()
                ->where('is_default', true)
                ->firstOrFail();
        }
        Log::info($items);


        // Variant IDs
        $variantIds = collect($items)
            ->unique()
            ->values();
        Log::info($variantIds);
        Log::info(ShopProductVariant::whereIn('id', $variantIds)->get());


        // All shop IDs
        $shopIds = ShopProductVariant::whereIn('id', $variantIds)
            ->pluck('shop_id')
            ->unique();

        Log::info($shopIds);


        // Paid shops only
        $paidShopIds = Shop::whereIn('id', $shopIds)
            ->where('is_free_delivery', false)
            ->pluck('id');

        Log::info($paidShopIds);

        // If all shops have free delivery
        if ($paidShopIds->isEmpty()) {
            Log::info("Helllllllllo2");

            return 0;
        }

        // Area IDs for paid shops
        $areaIds = Shop::whereIn('id', $paidShopIds)
            ->pluck('area_id')
            ->unique()
            ->toArray();
        Log::info("Helllllllllo");

        return DeliveryPricingService::calculateDeliveryFee(
            $address->area_id,
            $areaIds
        );
    }
}
