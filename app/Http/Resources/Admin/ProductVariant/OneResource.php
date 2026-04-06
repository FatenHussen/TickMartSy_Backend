<?php

namespace App\Http\Resources\Admin\ProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->getTranslations('name'),
            'sku' => $this->sku,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'price' => $this->product->price,
                'discount' => $this->product->discount,
                'country' => $this->product->country,
                'image' => $this->product->media->first()?->url ?? null,
                'images' => $this->product->media->map(function ($media) {
                    return $media->url;
                }),
                'category' => [
                    'id' => $this->product->category->id,
                    'name' => $this->product->category->name,
                ],
                'brand' => $this->product->brand ? [
                    'id' => $this->product->brand->id,
                    'name' => $this->product->brand->name,
                    'logo' => $this->product->brand->logo,
                ] : null,
            ],
            'attributes' => $this->getAttributesWithDetails(),
            'attributes_values_ids' => $this->attributes_values_ids,
            'is_trend' => $this->is_trend,
            'is_active' => (bool) $this->is_active,
            'shop_variants' => $this->shopVariants->map(function ($shopVariant) {
                return [
                    'id' => $shopVariant->id,
                    'shop' => [
                        'id' => $shopVariant->shop->id,
                        'name' => $shopVariant->shop->name,
                        'logo' => $shopVariant->shop->logo,
                        'address' => $shopVariant->shop->address,
                    ],
                    'price' => $shopVariant->price,
                    'quantity' => $shopVariant->quantity,
                    'sku' => $shopVariant->sku,
                    'barcode' => $shopVariant->barcode,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
