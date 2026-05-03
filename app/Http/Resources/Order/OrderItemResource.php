<?php

namespace App\Http\Resources\Order;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'note' => $this->note,
            'quantity' => $this->quantity,
            ...$this->withCurrency($this->unit_price, 'unit_price'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            ...$this->withCurrency($this->extras_total, 'extras_total'),
            ...$this->withCurrency($this->total, 'total'),
            'status' => $this->item_status,
            'variant_attributes' => $this->variant_attributes,
            'delivery_time' => $this->getDeliveryTime(),
            'extras' => $this->extras->map(function ($extra) {
                return [
                    'id' => $extra->extraDetail->id,
                    'detail_key' => $extra->extraDetail->detail_key,
                    'detail_value' => $extra->extraDetail->detail_value,
                    'price' => $extra->price,
                    'quantity' => $extra->quantity,
                    'price_currencies' => $this->dualCurrency($extra->price),
                ] ?? [];
            }),
        ];
    }

    private function getDeliveryTime(): ?string
    {
        $product = $this->shopProductVariant?->productVariant?->product;

        if (!$product) {
            return null;
        }

        return $product->effective_delivery_time;
    }
}
