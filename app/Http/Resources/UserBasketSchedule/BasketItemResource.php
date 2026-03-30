<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\UserBasketSchedule\BasketItemVariantResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        $availability = $this->resource->basket?->availability_items_by_id[$this->id] ?? null;

        $originalPrice = $this->price;
        $discountValue = $this->basket?->schedule?->discount_value ?? 0;
        $discountType = $this->basket?->schedule?->discount_type ?? null;

        $discountAmount = 0;
        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountAmount = round($originalPrice * $discountValue / 100, 2);
            } else {
                $discountAmount = round(min($discountValue, $originalPrice), 2);
            }
        }

        $priceAfterDiscount = $originalPrice - $discountAmount;

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            ...$this->withCurrency($originalPrice, 'original_price'),
            ...$this->withCurrency($discountAmount, 'discount_amount'),
            ...$this->withCurrency($priceAfterDiscount, 'price_after_discount'),
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'availability_status' => $availability['status'] ?? 'unknown',
            'is_available' => $availability['is_available'] ?? true,
            'available_quantity' => $availability['available_quantity'] ?? null,
            'resolved_shop_product_variant_id' => $availability['resolved_shop_product_variant_id'] ?? null,
            'product' => new BasketItemProductResource($this->whenLoaded('product')),
            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')),
        ];
    }
}
