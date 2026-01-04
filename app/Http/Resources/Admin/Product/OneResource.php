<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'full_description' => $this->getTranslations('full_description'),
            'country' => $this->getTranslations('country'),

            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'bought_with' => $this->bought_with,
            'is_instant_delivery' => $this->is_instant_delivery,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->getTranslations('name'),
            ],

            // ✅ Variants بدون IDs
            'variants' => $this->variants->map(fn($variant) => [
                'id' => $variant->id,
                'price' => $variant->price,
                'sku' => $variant->sku,

                'attributes' => $variant->attributesValues->map(fn($value) => [
                    'attribute' => $value->attribute->getTranslations('name'),
                    'value' => $value->getTranslations('value'),
                ]),
            ]),

            'category_details' => $this->categoryDetails->map(fn($detail) => [
                'id' => $detail->id,
                'name' => $detail->categoryDetail->getTranslations('name'),
                'value' => $detail->detail_value,
            ]),

            'extra_details' => $this->extraDetails->map(fn($detail) => [
                'id' => $detail->id,
                'key' => $detail->detail_key,
                'value' => $detail->detail_value,
            ]),

            'images' => $this->media->map(fn($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ]),
        ];
    }
}
