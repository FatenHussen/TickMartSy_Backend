<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $extrasPrice = $this->extras->sum('price');
        $priceAfterDiscount = $this->price * (1 - ($this->discount / 100));
        $finalPriceWithExtras = $priceAfterDiscount + $extrasPrice;

        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'discount' => $this->discount,
            'price_after_discount' => $priceAfterDiscount,
            'extras_price' => $extrasPrice,
            'final_price_with_extras' => $finalPriceWithExtras,
            'status' => $this->item_status,
            'variant_attributes' => $this->variant_attributes,
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
}
