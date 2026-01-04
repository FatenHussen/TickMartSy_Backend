<?php

// namespace App\Http\Resources\Admin\Product;

// use Illuminate\Http\Resources\Json\JsonResource;

// class OneResource extends JsonResource
// {
//     public function toArray($request)
//     {
//         return [
//             'id' => $this->id,

//             'name' => $this->getTranslations('name'),
//             'description' => $this->getTranslations('description'),
//             'full_description' => $this->getTranslations('full_description'),
//             'country' => $this->getTranslations('country'),

//             'price' => $this->price,
//             'price_after_discount' => $this->price_after_discount,
//             'quantity' => $this->quantity,

//             'sku' => $this->sku,
//             'model' => $this->model,
//             'barcode' => $this->barcode,
//             'time_prepare' => optional($this->time_prepare)->format('H:i'),
//             'bought_with' => $this->bought_with,
//             'is_instant_delivery' => $this->is_instant_delivery,

//             'category' => [
//                 'id' => $this->category?->id,
//                 'name' => $this->category?->getTranslations('name'),
//             ],

//             'variants' => $this->variants->map(fn($variant) => [
//                 'id' => $variant->id,
//                 'attributes' => $variant->attributesValues->map(fn($value) => [
//                     'attribute' => $value->attribute->getTranslations('name'),
//                     'value' => $value->getTranslations('value'),
//                 ]),
//                 'shops' => $variant->shopVariants->map(fn($sv) => [
//                     'shop_id' => $sv->shop_id,
//                     'shop_name' => $sv->shop->getTranslations('name'),
//                     'price' => $sv->price,
//                     'quantity' => $sv->quantity,
//                 ]),
//                 'images' => $variant->media->map(fn($img) => [
//                     'id' => $img->id,
//                     'path' => $img->path,
//                 ]),
//             ]),


//             'category_details' => $this->categoryDetails->map(fn($detail) => [
//                 'id' => $detail->id,
//                 'name' => $detail->categoryDetail->getTranslations('name'),
//                 'value' => $detail->detail_value,
//             ]),

//             'extra_details' => $this->extraDetails->map(fn($detail) => [
//                 'id' => $detail->id,
//                 'key' => $detail->detail_key,
//                 'value' => $detail->detail_value,
//             ]),

//             'images' => $this->media->map(fn($img) => [
//                 'id' => $img->id,
//                 'path' => $img->path,
//             ]),
//         ];
//     }
// }

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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
            'bought_with' => $this->bought_with ?? [],
            'is_instant_delivery' => $this->is_instant_delivery,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->getTranslations('name') ?? [],
            ],

            'variants' => ($this->variants ?? collect())->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'attributes' => ($variant->attributesValues)->map(function ($value) {
                        return [
                            'attribute' => $value->categoryAttribute?->name ?? [],
                            'value' => $value->name ?? [], 
                        ];
                    }),
                    'shops' => ($variant->shopVariants ?? collect())->map(function ($sv) {
                        return [
                            'shop_id' => $sv->shop_id,
                            'shop_name' => $sv->shop?->getTranslations('name') ?? [],
                            'price' => $sv->price,
                            'quantity' => $sv->quantity,
                        ];
                    }),

                    'images' => ($variant->media ?? collect())->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'path' => $img->path,
                        ];
                    }),
                ];
            })->values(),

            // Category Details
            'category_details' => ($this->categoryDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'name' => $detail->categoryDetail?->name,
                    'value' => $detail->detail_value ?? [],
                ];
            })->values(),

            // Extra Details
            'extra_details' => ($this->extraDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'key' => $detail->detail_key ?? [],
                    'value' => $detail->detail_value ?? [],
                ];
            })->values(),

            // Product Images
            'images' => ($this->media ?? collect())->map(function ($img) {
                return [
                    'id' => $img->id,
                    'path' => $img->path,
                ];
            })->values(),
        ];
    }
}
