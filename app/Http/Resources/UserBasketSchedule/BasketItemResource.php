<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        $availability = $this->resource->basket?->availability_items_by_id[$this->id] ?? null;
        $unitPrice = (float) $this->price;
        $quantity = (int) $this->quantity;
        $lineTotal = $unitPrice * $quantity;

        return [
            'id' => $this->id,
            'quantity' => $quantity,
            'unit' => $this->product?->unitOption?->name ?? $this->product?->unit,
            ...$this->withCurrency($unitPrice, 'original_price'),
            ...$this->withCurrency($lineTotal, 'line_total'),
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'shop' => $this->whenLoaded('variant', function () {
                $shop = $this->variant?->shop;
                if (!$shop) {
                    return null;
                }

                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'image' => $shop->image_url ?? null,
                ];
            }),
            'availability_status' => $availability['status'] ?? 'unknown',
            'is_available' => $availability['is_available'] ?? true,
            'available_quantity' => $availability['available_quantity'] ?? null,
            'resolved_shop_product_variant_id' => $availability['resolved_shop_product_variant_id'] ?? null,
            'product' => new BasketItemProductResource($this->whenLoaded('product')),
            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')),
        ];
    }
}
