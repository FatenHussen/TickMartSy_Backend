<?php


namespace App\Http\Resources\Product;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'full_description' => $this->full_description,
            'country' => $this->country,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'bought_with' => !empty($this->bought_with)
                ? AllResource::collection(
                    Product::whereIn('id', $this->bought_with)->get()
                )
                : [],
            'is_instant_delivery' => $this->is_instant_delivery,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],

            'variants' => ($this->variants ?? collect())->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'attributes' => collect($variant->attributesValues)->map(function ($value) {
                        return [
                            'attribute' => $value->categoryAttribute?->name,
                            'value' => $value->name,
                        ];
                    }),

                    'shops' => ($variant->shopVariants ?? collect())->map(function ($sv) {
                        return [
                            'shop_id' => $sv->shop_id,
                            'shop_name' => $sv->shop?->name,
                            'price' => $sv->price,
                            'quantity' => $sv->quantity,
                        ];
                    })->values(),

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
                    'value' => $detail->detail_value
                ];
            })->values(),

            // Extra Details
            'extra_details' => ($this->extraDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'key' => $detail->detail_key,
                    'value' => $detail->detail_value,
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
