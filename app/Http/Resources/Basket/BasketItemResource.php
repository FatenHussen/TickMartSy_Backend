<?php

namespace App\Http\Resources\Basket;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            ...$this->withCurrency($this->price, 'unit_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
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

        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return $variants->map(function ($variant) use ($currencyId) {

            $product = optional($variant->productVariant)->product;
            $brand   = optional($product)->brand;

            $priceData = $this->convertPrice($variant->price, $currencyId);

            return [
                'product_id' => $product->id ?? null,
                'shop_product_variant_id' => $variant->id,

                'name' => trim(
                    ($product->name ?? '') . ' ' . ($brand->name ?? '')
                ),

                'image_url' => optional($product->media->first())->url,

                'price' => $priceData['amount'],
                'price_formatted' => $priceData['formatted'],
                'currency' => $priceData['currency'],
                'currency_symbol' => $priceData['symbol'],
            ];
        })->values();
    }

}
