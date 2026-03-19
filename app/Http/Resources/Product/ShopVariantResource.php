<?php

namespace App\Http\Resources\Product;

use App\Services\Base\LocationService;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopVariantResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        $shopId = $request->get('shop_id');

        if (!$shopId) {
            $shopId = app(LocationService::class)->getNearestShopId(
                $request->query('lat'),
                $request->query('lng')
            );
        }

        $shopVariant = $shopId
            ? $this->shopVariants->firstWhere('shop_id', $shopId)
            : null;

        if (!$shopVariant) {
            $shopVariant = $this->shopVariants->first();
        }

        if (!$shopVariant) {
            return null;
        }

        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id' => $shopVariant->id,
            // 'variant_id' => $this->id,
            'variant_id' =>$shopVariant->id,
            'attributes' => VariantAttributeResource::collection(
                $this->attributesValues
            ),
            ...$this->withCurrency($shopVariant->price, 'price'),
            'quantity' => $shopVariant->quantity,
            'shop_id'  => $shopVariant->shop_id,
            'images'   => MediaResource::collection(
                $this->media
            ),
        ];
    }

}
