<?php

namespace App\Http\Resources\Admin\ShopProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'shop_id' => $this->shop_id,
            'product' => [
                'id' => $this->productVariant->product->id,
                'name' => $this->productVariant->product->name,
                'description' => $this->productVariant->product->description,
                'price' => $this->productVariant->product->price,
                'discount' => $this->productVariant->product->discount,
                'country' => $this->productVariant->product->country,
                'image' => $this->productVariant->product->media->first()?->url ?? null,
                'images' => $this->productVariant->product->media->map(function ($media) {
                    return $media->url;
                }),
                'category' => [
                    'id' => $this->productVariant->product->category->id,
                    'name' => $this->productVariant->product->category->name,
                ],
                'brand' => $this->productVariant->product->brand ? [
                    'id' => $this->productVariant->product->brand->id,
                    'name' => $this->productVariant->product->brand->name,
                    'logo' => $this->productVariant->product->brand->logo,
                ] : null,
            ],
            'attributes' => $this->productVariant->getAttributesWithDetails(),
            'attributes_values_ids' => $this->productVariant->attributes_values_ids,
            'is_trend' => $this->productVariant->is_trend,
            'shop' => [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
                'logo' => $this->shop->logo,
                'address' => $this->shop->address,
                'email' => $this->shop->email,
                'mobile' => $this->shop->mobile,
            ],
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'quantity' => $this->quantity,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
