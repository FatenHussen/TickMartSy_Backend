<?php

namespace App\Services\User;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\NotFoundException;
use App\Http\Resources\User\Cart\CartItemResource;
use App\Models\CartItem;
use App\Models\ShopProductVariant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CartService
{
    public function list(int $userId): AnonymousResourceCollection
    {
        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with($this->itemRelations())
            ->orderByDesc('id')
            ->get();

        return CartItemResource::collection($items);
    }

    public function add(int $userId, array $data): CartItemResource
    {
        $shopVariant = $this->findShopVariant((int) $data['shop_product_variant_id']);
        $quantity = (int) ($data['quantity'] ?? 1);

        $item = CartItem::query()->firstOrNew([
            'user_id' => $userId,
            'shop_product_variant_id' => $shopVariant->id,
        ]);

        $nextQty = ($item->exists ? (int) $item->quantity : 0) + $quantity;
        $this->assertQuantity($shopVariant, $nextQty);

        $item->quantity = $nextQty;
        if (array_key_exists('note', $data)) {
            $item->note = $data['note'];
        }
        $item->save();

        return new CartItemResource($item->load($this->itemRelations()));
    }

    public function update(int $userId, int $itemId, array $data): CartItemResource
    {
        $item = $this->findItem($userId, $itemId);
        $shopVariant = $this->findShopVariant((int) $item->shop_product_variant_id);

        if (isset($data['quantity'])) {
            $this->assertQuantity($shopVariant, (int) $data['quantity']);
            $item->quantity = (int) $data['quantity'];
        }

        if (array_key_exists('note', $data)) {
            $item->note = $data['note'];
        }

        $item->save();

        return new CartItemResource($item->load($this->itemRelations()));
    }

    public function remove(int $userId, int $itemId): bool
    {
        $this->findItem($userId, $itemId)->delete();

        return true;
    }

    private function findItem(int $userId, int $itemId): CartItem
    {
        $item = CartItem::query()
            ->where('user_id', $userId)
            ->whereKey($itemId)
            ->first();

        if (!$item) {
            throw new NotFoundException();
        }

        return $item;
    }

    private function findShopVariant(int $id): ShopProductVariant
    {
        $shopVariant = ShopProductVariant::query()
            ->with(['productVariant.product', 'shop'])
            ->find($id);

        if (!$shopVariant) {
            throw new NotFoundException();
        }

        return $shopVariant;
    }

    private function assertQuantity(ShopProductVariant $shopVariant, int $quantity): void
    {
        $stock = (int) ($shopVariant->productVariant?->quantity ?? 0);

        if ($stock < 1 || $quantity > $stock) {
            throw new CustomExceptionWithMessage('custom.cart_quantity_unavailable');
        }

        $maxPurchase = $shopVariant->productVariant?->product?->max_purchase_quantity;
        if ($maxPurchase !== null && $quantity > (int) $maxPurchase) {
            throw new CustomExceptionWithMessage('custom.cart_quantity_unavailable');
        }
    }

    private function itemRelations(): array
    {
        return [
            'shopProductVariant.productVariant.product.media',
            'shopProductVariant.shop',
        ];
    }
}
