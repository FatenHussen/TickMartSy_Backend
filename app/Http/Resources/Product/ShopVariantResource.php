<?php

namespace App\Http\Resources\Product;

use App\Services\Base\LocationService;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopVariantResource extends JsonResource
{
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

        return [
            'variant_id' => $this->id,
            'attributes' => VariantAttributeResource::collection(
                $this->attributesValues
            ),
            'price'    => $shopVariant->price,
            'quantity' => $shopVariant->quantity,
            'shop_id'  => $shopVariant->shop_id,
            'images'   => MediaResource::collection(
                $this->media
            ),
        ];
    }

}
