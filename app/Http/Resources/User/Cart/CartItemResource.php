<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $shopVariant = $this->shopProductVariant;
        $productVariant = $shopVariant?->productVariant;
        $product = $productVariant?->product;

        return [
            'id' => $this->id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'shop_id' => $shopVariant?->shop_id,
            'quantity' => $this->quantity,
            'note' => $this->note,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
            ] : null,
            'sku' => $productVariant?->sku,
            'stock_quantity' => $productVariant?->quantity,
        ];
    }
}
