<?php

namespace App\Services\User;

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ShopProductVariant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PurchasableVariantService
{
    public function resolve(
        mixed $id,
        mixed $quantity,
        string $field = 'shop_product_variant_id',
        bool $lockForUpdate = false,
    ): ShopProductVariant {
        $quantityField = $this->quantityField($field);
        $validator = Validator::make(
            ['identity' => $id, 'quantity' => $quantity],
            ['identity' => ['required', 'integer', 'min:1'], 'quantity' => ['required', 'integer', 'min:1']],
        );
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors[$key === 'identity' ? $field : $quantityField] = $messages;
            }
            throw ValidationException::withMessages($errors);
        }

        $offer = ShopProductVariant::query()->with(['productVariant.product', 'shop'])->find((int) $id);
        if (! $offer) {
            throw ValidationException::withMessages([$field => __('validation.exists', ['attribute' => $field])]);
        }

        // Shop rows share inventory on the underlying product variant.
        if ($lockForUpdate) {
            $offer->setRelation('productVariant', ProductVariant::query()
                ->with('product')->lockForUpdate()->find($offer->product_variant_id));
        }
        $variant = $offer->productVariant;
        $product = $variant?->product;
        if (! $variant || $variant->trashed() || ! $variant->is_active
            || ! $product || $product->trashed() || ! $product->is_active || ! $product->is_visible
            || $product->approval_status !== ProductApprovalStatus::APPROVED
            || ! $offer->shop || ! $offer->shop->is_active
            || in_array((int) $product->category_id, Category::idsHiddenFromCatalog(), true)) {
            throw ValidationException::withMessages([$field => __('custom.cart_quantity_unavailable')]);
        }

        $this->assertQuantity($offer, (int) $quantity, $quantityField);

        return $offer;
    }

    public function assertQuantity(ShopProductVariant $offer, int $quantity, string $field = 'quantity'): void
    {
        $stock = (int) ($offer->productVariant?->quantity ?? 0);
        $limit = $offer->productVariant?->product?->max_purchase_quantity;
        if ($quantity < 1 || $stock < 1 || $quantity > $stock
            || ($limit !== null && $quantity > (int) $limit)) {
            throw ValidationException::withMessages([$field => __('custom.cart_quantity_unavailable')]);
        }
    }

    /** Validate combined demand, including duplicate rows and offers sharing inventory. */
    public function validateItems(array $items, bool $lockForUpdate = false): array
    {
        Validator::make(['items' => $items], [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ])->validate();

        $offers = [];
        $variantQuantities = [];
        $productQuantities = [];
        if ($lockForUpdate) {
            // Lock shared inventory in a fixed order, independent of the customer's item order.
            $ids = collect($items)->pluck('shop_product_variant_id')
                ->filter(fn ($id) => is_scalar($id) && filter_var($id, FILTER_VALIDATE_INT) !== false);
            $variantIds = ShopProductVariant::query()->whereIn('id', $ids)->pluck('product_variant_id');
            ProductVariant::query()->whereIn('id', $variantIds)->orderBy('id')->lockForUpdate()->get();
        }
        foreach ($items as $index => $item) {
            $offer = $this->resolve(
                $item['shop_product_variant_id'] ?? null,
                $item['quantity'] ?? null,
                "items.{$index}.shop_product_variant_id",
                $lockForUpdate,
            );
            $variant = $offer->productVariant;
            $product = $variant->product;
            $quantity = (int) $item['quantity'];
            $variantQuantities[$variant->id] = ($variantQuantities[$variant->id] ?? 0) + $quantity;
            $productQuantities[$product->id] = ($productQuantities[$product->id] ?? 0) + $quantity;
            $this->assertQuantity($offer, $variantQuantities[$variant->id], "items.{$index}.quantity");
            if ($product->max_purchase_quantity !== null
                && $productQuantities[$product->id] > (int) $product->max_purchase_quantity) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => __('custom.cart_quantity_unavailable'),
                ]);
            }
            $offers[$index] = $offer;
        }

        return $offers;
    }

    private function quantityField(string $field): string
    {
        $dot = strrpos($field, '.');

        return $dot === false ? 'quantity' : substr($field, 0, $dot + 1).'quantity';
    }
}
