<?php

namespace App\Services\User;

use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Services\Base\DeliveryPricingService;

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

        // Variant IDs
        $variantIds = collect($items)
            ->unique()
            ->values();

        // All shop IDs
        $shopIds = ShopProductVariant::whereIn('id', $variantIds)
            ->pluck('shop_id')
            ->unique();

        // Paid shops only
        $paidShopIds = Shop::whereIn('id', $shopIds)
            ->where('is_free_delivery', false)
            ->pluck('id');

        // If all shops have free delivery
        if ($paidShopIds->isEmpty()) {
            return 0;
        }

        // Area IDs for paid shops
        $areaIds = Shop::whereIn('id', $paidShopIds)
            ->pluck('area_id')
            ->unique()
            ->toArray();

        return DeliveryPricingService::calculateDeliveryFee(
            $address->area_id,
            $areaIds
        );
    }
}
