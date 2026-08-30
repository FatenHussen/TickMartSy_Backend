<?php

namespace App\Http\Resources\Product;

use App\Models\AttributeValue;
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

        $images = $this->media;
        if (!$images || $images->isEmpty()) {
            $images = $this->product?->media ?? collect();
        }

        return [
            'id' => $shopVariant?->id,
            'variant_id' => $shopVariant?->id ?? $this->id,
            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'attributes' => VariantAttributeResource::collection(
                AttributeValue::with(['categoryAttribute', 'color'])
                    ->whereIn('id', $this->attributes_values_ids ?? [])
                    ->get()
            ),
            ...$this->withCurrency($this->price, 'price'),
            'discount_value' => $this->discount ?? 0,
            'discount_type' => $this->discount_type ?? 'none',
            ...$this->withCurrency($this->discount_amount, 'discount'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'quantity' => $this->quantity,
            'shop_id'  => $shopVariant?->shop_id,
            'is_restaurant' => (bool) ($shopVariant?->shop?->is_restaurant ?? $this->product?->is_restaurant ?? false),
            'city_id' => $shopVariant?->shop?->city_id ?? $shopVariant?->shop?->area?->city_id,
            'images'   => MediaResource::collection($images),
        ];
    }

}
