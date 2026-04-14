<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'final_price' => $this->final_price,
            'subtotal' => $this->subtotal,
            'extras_total' => $this->extras_total,
            'total' => $this->total,
            'status' => $this->item_status,
            'variant_attributes' => $this->variant_attributes,
            'delivery_time' => $this->getDeliveryTime(),
            'extras' => $this->extras->map(function ($extra) {
                return [
                    'id' => $extra->extraDetail->id,
                    'detail_key' => $extra->extraDetail->detail_key,
                    'detail_value' => $extra->extraDetail->detail_value,
                    'price' => $extra->price,
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
