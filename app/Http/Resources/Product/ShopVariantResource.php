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
            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'attributes' => VariantAttributeResource::collection(
                $this->attributesValues
            ),
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->discount, 'discount'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'quantity' => $this->quantity,
            'shop_id'  => $shopVariant->shop_id,
            'is_restaurant' => (bool) ($shopVariant->shop?->is_restaurant ?? false),
            'city_id' => $shopVariant->shop?->city_id ?? $shopVariant->shop?->area?->city_id,
            'images'   => MediaResource::collection(
                $this->media
            ),
        ];
    }

}
