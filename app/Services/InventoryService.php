<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\ShopProductVariant;
use App\Events\LowStockDetected;
use App\Exceptions\CustomExceptionWithMessage;

class InventoryService
{
    public function decreaseStock(int $shopProductVariantId, int $quantity): void
    {
        $variant = $this->lockProductVariant($shopProductVariantId);

        if (!is_null($variant->quantity) && $variant->quantity < $quantity) {
            throw new CustomExceptionWithMessage('custom.orders.insufficient_stock_generic');
        }

        if (!is_null($variant->quantity)) {
            $variant->decrement('quantity', $quantity);
            $variant->product?->syncQuantityFromVariants();

            if ($variant->quantity <= 5) {
                LowStockDetected::dispatch($variant);
            }
        }
    }

    public function increaseStock(int $shopProductVariantId, int $quantity): void
    {
        $variant = $this->lockProductVariant($shopProductVariantId);

        if (!is_null($variant->quantity)) {
            $variant->increment('quantity', $quantity);
            $variant->product?->syncQuantityFromVariants();
        }
    }

    private function lockProductVariant(int $shopProductVariantId): ProductVariant
    {
        $shopVariant = ShopProductVariant::query()
            ->lockForUpdate()
            ->findOrFail($shopProductVariantId);

        return ProductVariant::query()
            ->lockForUpdate()
            ->findOrFail($shopVariant->product_variant_id);
    }
}
