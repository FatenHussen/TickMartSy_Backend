<?php

namespace App\Http\Resources\Admin\ShopProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variantImage = $this->productVariant?->media
            ?->where('collection', 'variant')
            ?->sortBy('order')
            ?->first()?->url;

        $productFallbackImage = $this->productVariant?->product?->media
            ?->sortBy('order')
            ?->first()?->url;

        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'variant' => [
                'id' => $this->productVariant?->id,
                'name' => $this->productVariant?->name,
                'sku' => $this->productVariant?->sku,
                'model' => $this->productVariant?->model,
                'barcode' => $this->productVariant?->barcode,
            ],
            'shop_id' => $this->shop_id,
            'is_restaurant' => (bool) ($this->shop?->is_restaurant ?? false),
            'city_id' => $this->shop?->city_id ?? $this->shop?->area?->city_id,
            'variant_image' => $variantImage ?? $productFallbackImage,
            'product' => [
                'id' => $this->productVariant->product->id,
                'name' => $this->productVariant->product->name,
                'description' => $this->productVariant->product->description,
                'price' => $this->productVariant->product->price,
                'discount' => $this->productVariant->product->discount,
                'country' => $this->productVariant->product->country,
                'image' => $variantImage ?? $productFallbackImage,
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
            'shop' => $this->shop ? [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
                'logo' => $this->shop->logo,
                'address' => $this->shop->address,
                'email' => $this->shop->email,
                'mobile' => $this->shop->mobile,
                'is_restaurant' => (bool) ($this->shop->is_restaurant ?? false),
                'city_id' => $this->shop->city_id ?? $this->shop?->area?->city_id,
            ] : null,
            'price' => $this->price,
            'discount' => $this->discount,
            'price_after_discount' => $this->price_after_discount,
            'cost_price' => $this->cost_price,
            'quantity' => $this->quantity,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
