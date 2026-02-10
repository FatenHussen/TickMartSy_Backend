<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => $this->subtotal,
            'is_required' => $this->is_required,
            'is_extra' => $this->is_extra,
            'min_quantity' => (int) $this->min_quantity,
            'max_quantity' => (int) $this->max_quantity,
            'can_adjust' => $this->canAdjustQuantity(),
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'product' =>new BasketItemProductResource($this->whenLoaded('product')) ?? null,

            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')) ?? null,
            'alternatives' => $this->getAlternatives(),

            // 'companies' => BasketItemCompanyResource::collection($this->whenLoaded('companies')) ??  [],
        ];
    }
    private function getAlternatives()
    {
        if (empty($this->shop_product_variant_ids)) {
            return [];
        }

        $variants = \App\Models\ShopProductVariant::query()
            ->whereIn('id', $this->shop_product_variant_ids)
            ->with([
                'productVariant.product.brand',
                'productVariant.product.media'
            ])
            ->get();

        return $variants->map(function ($variant) {

            $product = optional($variant->productVariant)->product;
            $brand   = optional($product)->brand;

            return [
                'product_id' => $product->id ?? null,
                'shop_product_variant_id' => $variant->id,

                // اسم المنتج + الشركة
                'name' => trim(
                    ($product->name ?? '') . ' ' . ($brand->name ?? '')
                ),

                'image_url' => optional($product->media->first())->url,

                'price' => (float) $variant->price,
            ];
        })->values();
    }

}
