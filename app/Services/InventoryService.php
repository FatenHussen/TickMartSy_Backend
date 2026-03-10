<?php

namespace App\Services;

use App\Models\ShopProductVariant;
use App\Events\LowStockDetected;
use Exception;

class InventoryService
{
    public function decreaseStock(int $variantId, int $quantity): void
    {
        $variant = ShopProductVariant::lockForUpdate()->findOrFail($variantId);

        if (!is_null($variant->quantity) && $variant->quantity < $quantity) {
            throw new Exception('Insufficient stock');
        }

        if (!is_null($variant->quantity)) {

            $variant->decrement('quantity', $quantity);

            if ($variant->quantity <= 5) {
                LowStockDetected::dispatch($variant);
            }
        }
    }

    public function increaseStock(int $variantId, int $quantity): void
    {
        $variant = ShopProductVariant::lockForUpdate()->findOrFail($variantId);

        if (!is_null($variant->quantity)) {
            $variant->increment('quantity', $quantity);
        }
    }
}
