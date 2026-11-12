<?php

namespace App\Services;

use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Order;
use App\Models\ShopProductVariant;
use App\Models\UserBasketSchedule;
use App\Models\UserBasketScheduleItem;
use App\Services\User\PurchasableVariantService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScheduledBasketAvailabilityService
{
    private const ISSUE_STATUSES = [
        'insufficient_quantity',
        'out_of_stock',
        'variant_deleted',
        'unavailable',
    ];

    public function evaluateUserSchedule(UserBasketSchedule $basket): array
    {
        $basket->loadMissing([
            'items.product',
            'items.variant.productVariant.product',
            'schedule',
        ]);

        $items = $basket->items
            ->map(fn (UserBasketScheduleItem $item) => $this->evaluateUserScheduleItem($item))
            ->values();

        return $this->buildSummary(
            basketType: 'user_schedule',
            basketId: $basket->id,
            nextRunDate: $basket->next_run_date?->format('Y-m-d'),
            items: $items,
        );
    }

    public function evaluateAdminSchedule(Basket $basket, ?Order $latestOrder = null): array
    {
        $basket->loadMissing([
            'items.product',
            'items.shopProductVariant.productVariant.product',
            'schedules',
        ]);

        $items = $basket->items
            ->map(fn (BasketItem $item) => $this->evaluateAdminScheduleItem($item))
            ->values();

        return $this->buildSummary(
            basketType: 'admin_schedule',
            basketId: $basket->id,
            nextRunDate: $latestOrder?->next_run_date?->format('Y-m-d'),
            items: $items,
        );
    }

    private function evaluateUserScheduleItem(UserBasketScheduleItem $item): array
    {
        $variant = $item->variant;
        [$status, $availableQuantity] = $this->resolveVariantStatus($variant, (int) $item->quantity);

        return [
            'item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name,
            'required_quantity' => (int) $item->quantity,
            'available_quantity' => $availableQuantity,
            'status' => $status,
            'is_available' => ! in_array($status, self::ISSUE_STATUSES, true),
            'resolved_shop_product_variant_id' => $variant?->id,
            'resolved_shop_product_variant_price' => $variant?->productVariant?->price,
        ];
    }

    private function evaluateAdminScheduleItem(BasketItem $item): array
    {
        $requiredQuantity = (int) $item->quantity;
        $primaryVariant = $item->shopProductVariant;

        [$primaryStatus, $primaryAvailableQuantity] = $this->resolveVariantStatus($primaryVariant, $requiredQuantity);

        if ($primaryStatus === 'available') {
            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'required_quantity' => $requiredQuantity,
                'available_quantity' => $primaryAvailableQuantity,
                'status' => 'available',
                'is_available' => true,
                'resolved_shop_product_variant_id' => $primaryVariant?->id,
                'resolved_shop_product_variant_price' => $primaryVariant?->productVariant?->price,
            ];
        }

        $alternatives = $this->loadAlternativeVariants($item);
        $resolvedAlternative = $alternatives->first(function (ShopProductVariant $variant) use ($requiredQuantity) {
            [$status] = $this->resolveVariantStatus($variant, $requiredQuantity);

            return $status === 'available';
        });

        if ($resolvedAlternative) {
            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'required_quantity' => $requiredQuantity,
                'available_quantity' => $resolvedAlternative->productVariant?->quantity,
                'status' => 'available_with_alternative',
                'is_available' => true,
                'resolved_shop_product_variant_id' => $resolvedAlternative->id,
                'resolved_shop_product_variant_price' => $resolvedAlternative->productVariant?->price,
            ];
        }

        $availableQuantities = $alternatives
            ->map(fn (ShopProductVariant $variant) => $variant->productVariant?->quantity)
            ->filter(fn ($quantity) => $quantity !== null)
            ->push($primaryAvailableQuantity)
            ->filter(fn ($quantity) => $quantity !== null)
            ->values();

        $fallbackStatus = $primaryStatus;

        if ($fallbackStatus === 'variant_deleted' && $availableQuantities->max() > 0) {
            $fallbackStatus = 'insufficient_quantity';
        }

        if ($fallbackStatus !== 'insufficient_quantity' && $availableQuantities->max() > 0 && $availableQuantities->max() < $requiredQuantity) {
            $fallbackStatus = 'insufficient_quantity';
        }

        if ($fallbackStatus === 'variant_deleted' && $availableQuantities->isNotEmpty() && $availableQuantities->max() === 0) {
            $fallbackStatus = 'out_of_stock';
        }

        return [
            'item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name,
            'required_quantity' => $requiredQuantity,
            'available_quantity' => $availableQuantities->max(),
            'status' => $fallbackStatus,
            'is_available' => false,
            'resolved_shop_product_variant_id' => $primaryVariant?->id,
            'resolved_shop_product_variant_price' => $primaryVariant?->productVariant?->price,
        ];
    }

    private function loadAlternativeVariants(BasketItem $item): Collection
    {
        if (empty($item->shop_product_variant_ids)) {
            return collect();
        }

        return ShopProductVariant::query()
            ->with('productVariant')
            ->whereIn('id', $item->shop_product_variant_ids)
            ->get();
    }

    private function resolveVariantStatus(?ShopProductVariant $variant, int $requiredQuantity): array
    {
        if (! $variant) {
            return ['variant_deleted', null];
        }

        $availableQty = $variant->productVariant?->quantity;

        if ($availableQty === null || $availableQty <= 0) {
            return ['out_of_stock', $availableQty === null ? null : 0];
        }

        try {
            // The same sellability rules apply to basket status and purchase writes.
            app(PurchasableVariantService::class)->resolve($variant->id, 1);
        } catch (ValidationException) {
            return ['unavailable', (int) $availableQty];
        }

        if ($availableQty < $requiredQuantity) {
            return ['insufficient_quantity', (int) $availableQty];
        }

        $limit = $variant->productVariant?->product?->max_purchase_quantity;
        if ($limit !== null && $requiredQuantity > (int) $limit) {
            return ['unavailable', (int) $availableQty];
        }

        return ['available', (int) $availableQty];
    }

    private function buildSummary(string $basketType, int $basketId, ?string $nextRunDate, Collection $items): array
    {
        $itemsById = $items->keyBy('item_id');
        $issueItems = $items
            ->filter(fn (array $item) => in_array($item['status'], self::ISSUE_STATUSES, true))
            ->values();
        $warningItems = $items
            ->filter(fn (array $item) => $item['status'] === 'available_with_alternative')
            ->values();

        $status = 'available';

        if ($issueItems->isNotEmpty()) {
            $status = 'has_unavailable_items';
        } elseif ($warningItems->isNotEmpty()) {
            $status = 'available_with_alternative';
        }

        return [
            'basket_type' => $basketType,
            'basket_reference_id' => $basketId,
            'status' => $status,
            'has_issue' => $issueItems->isNotEmpty(),
            'unavailable_items_count' => $issueItems->count(),
            'warning_items_count' => $warningItems->count(),
            'next_run_date' => $nextRunDate,
            'items' => $items->values()->all(),
            'items_by_id' => $itemsById->all(),
            'issue_items' => $issueItems->all(),
        ];
    }
}
