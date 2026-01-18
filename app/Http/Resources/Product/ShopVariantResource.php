<?php

namespace App\Http\Resources\Product;

use App\Services\Base\LocationService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ShopVariantResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Collection $variants */
        $variants = $this->resource instanceof Collection
            ? $this->resource
            : collect([$this->resource]);

        $shopId = $request->get('shop_id');
        if (!$shopId) {
            $shopId = app(LocationService::class)->getNearestShopId(
                $request->query('lat'),
                $request->query('lng')
            );
        }

        if (!$shopId) return [];

        $result = [];

        foreach ($variants as $variant) {

            $shopVariant = $variant->shopVariants
                ->firstWhere('shop_id', $shopId);

            if (!$shopVariant) {
                continue;
            }

            $result[] = [
                'variant_id' => $variant->id,
                'attributes' => VariantAttributeResource::collection(
                    $variant->attributesValues
                ),
                'price'    => $shopVariant->price,
                'quantity' => $shopVariant->quantity,
                'images'   => MediaResource::collection(
                    $variant->media
                ),
            ];
        }

        return $result;
    }
}
