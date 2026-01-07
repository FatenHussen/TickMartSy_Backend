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
            'attributes_map' => $this->buildAttributesMap(),

            'shop_variants' => $this->buildShopVariantsList(),
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
    protected function buildAttributesMap()
    {
        $map = [];

        foreach ($this->variants as $variant) {
            foreach ($variant->attributesValues as $attrValue) {
                $attrName = $attrValue?->categoryAttribute?->name;
                $value    = $attrValue->name;

                if (!$attrName) continue;

                if (!isset($map[$attrName])) {
                    $map[$attrName] = [];
                }

                if (!in_array($value, $map[$attrName])) {
                    $map[$attrName][] = $value;
                }
            }
        }

        return collect($map)->map(function ($values, $attrName) {
            return [
                'attribute' => $attrName,
                'values'    => array_values($values),
            ];
        })->values();
    }
    protected function buildShopVariantsList()
    {
        $shopId = request()->get('shop_id');

        if (!$shopId) return [];

        return $this->variants->map(function ($variant) use ($shopId) {

            $shopVariant = $variant->shopVariants
                ->firstWhere('shop_id', $shopId);

            if (!$shopVariant) return null;

            return [
                'variant_id' => $variant->id,

                'attributes' => $variant->attributesValues->map(function ($attr) {
                    return [
                        'attribute' => $attr?->categoryAttribute?->name,
                        'value'     => $attr->name,
                    ];
                })->values(),

                'price'    => $shopVariant->price,
                'quantity' => $shopVariant->quantity,
                'images' => ($variant->media ?? collect())->map(function ($img) {
                    return [
                        'id'   => $img->id,
                        'path' => $img->path,
                    ];
                })->values(),
            ];
        })->filter()->values();
    }
}
// class OneResource extends JsonResource
// {
//     public function toArray($request)
//     {
//         return [
//             'id' => $this->id,
//             'name' => $this->name,
//             'description' => $this->description,
//             'full_description' => $this->full_description,
//             'country' => $this->country,
//             'time_prepare' => $this->time_prepare,

//             'category' => $this->category?->name,

//             'attributes_map' => $this->buildAttributesMap(),

//             'variants_list' => $this->buildVariantsList(),

//             'category_details' => $this->categoryDetails->map(fn($cd) => [
//                 'id' => $cd->id,
//                 'name' => $cd->categoryDetail?->name,
//                 'value' => $cd->detail_value,
//             ])->values(),

//             'extra_details' => $this->extraDetails->map(fn($ed) => [
//                 'id' => $ed->id,
//                 'key' => $ed->detail_key,
//                 'value' => $ed->detail_value,
//             ])->values(),

//             'images' => $this->media->map(fn($img) => [
//                 'id' => $img->id,
//                 'path' => $img->path,
//             ])->values(),
//         ];
//     }

//     // =========================
//     // مصفوفة Attributes Map
//     // =========================
//     protected function buildAttributesMap()
//     {
//         $map = [];

//         foreach ($this->variants as $variant) {
//             foreach ($variant->attributesValues as $attrValue) {
//                 $attrName = $attrValue?->categoryAttribute->name ?? 'غير معروف';
//                 $value = $attrValue->name;

//                 if (!isset($map[$attrName])) {
//                     $map[$attrName] = [];
//                 }
//                 if (!in_array($value, $map[$attrName])) {
//                     $map[$attrName][] = $value;
//                 }
//             }
//         }

//         return collect($map)->map(fn($values, $name) => [
//             'attribute' => $name,
//             'values' => array_values($values),
//         ])->values();
//     }

//     // =========================
//     // مصفوفة Variants List حسب shop
//     // =========================
//     protected function buildVariantsList()
//     {
//         $shopId = request()->get('shop_id');

//         return $this->variants->map(function ($variant) use ($shopId) {

//             $shopVariant = $variant->shopVariants
//                 ->firstWhere('shop_id', $shopId);

//             if (!$shopVariant) return null;

//             return [
//                 'variant_id' => $variant->id,

//                 'values' => $variant->attributesValues->map(fn($attr) => [
//                     'attribute' => $attr?->categoryAttribute->name,
//                     'value'     => $attr->name,
//                 ])->values(),

//                 'price'    => $shopVariant->price,
//                 'quantity' => $shopVariant->quantity,

//                 'images' => $variant->images->map(fn($img) => [
//                     'id'   => $img->id,
//                     'path' => $img->path,
//                 ])->values(),
//             ];
//         })->filter()->values();
//     }
// }
