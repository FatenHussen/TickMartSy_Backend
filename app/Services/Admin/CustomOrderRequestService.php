<?php

namespace App\Services\Admin;

use App\Enums\CartType;
use App\Enums\CustomOrderRequestStatus;
use App\Enums\OrderStatus;
use App\Enums\PriceVarianceType;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\CustomOrderRequest\AllResource;
use App\Http\Resources\CustomOrderRequest\OneResource;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShopProductVariant;
use App\Services\Base\NotificationService;
use App\Services\BaseService;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class CustomOrderRequestService extends BaseService
{
    public function __construct(
        CustomOrderRequest $model,
        private readonly NotificationService $notificationService,
    ) {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['address', 'paymentMethod', 'order.items', 'user'];
        $this->pagination = true;
        $this->searchableFields = ['id', 'description'];
        $this->sortableFields = ['id', 'created_at', 'expected_at'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    public function convert(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $request = CustomOrderRequest::query()
                ->with(['user', 'address', 'paymentMethod'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($request->status !== CustomOrderRequestStatus::PENDING_PRICING) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.already_priced', 422);
            }

            $paymentMethod = $this->resolvePaymentMethod($request);
            $hasExternal = collect($data['items'])->contains(fn ($item) => ($item['type'] ?? '') === 'external');
            $deliveryPrice = (float) ($data['delivery_price'] ?? 0);

            [
                $subtotal,
                $totalQuantity,
            ] = $this->buildOrderItemsPreview($data['items']);

            $total = $subtotal + $deliveryPrice;

            $order = Order::create([
                'user_id' => $request->user_id,
                'user_address_id' => $request->user_address_id,
                'payment_method_id' => $paymentMethod->id,
                'custom_order_request_id' => $request->id,
                'cart_type' => CartType::CUSTOM->value,
                'is_instant_delivery' => (bool) ($data['is_instant_delivery'] ?? false),
                'status' => OrderStatus::WAITING_APPROVAL->value,
                'is_paid' => false,
                'has_external_items' => $hasExternal,
                'price_variance_type' => $hasExternal ? ($data['price_variance_type'] ?? null) : null,
                'price_variance_value' => $hasExternal ? ($data['price_variance_value'] ?? null) : null,
                'approximate_total' => $data['approximate_total'] ?? null,
                'subtotal' => $subtotal,
                'delivery_price' => $deliveryPrice,
                'original_delivery_price' => $deliveryPrice,
                'total_quantity' => $totalQuantity,
                'total' => $total,
                'waiting_approval_at' => now(),
            ]);

            $this->persistOrderItems($order, $data['items']);

            $request->update([
                'order_id' => $order->id,
                'status' => CustomOrderRequestStatus::WAITING_APPROVAL->value,
                'admin_note' => $data['admin_note'] ?? $request->admin_note,
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                null,
                OrderStatus::WAITING_APPROVAL->value,
                'admin'
            );

            $this->notifyUserPriced($request->fresh($this->relations), $order->fresh('items'));

            return new OneResource($request->fresh($this->relations));
        });
    }

    public function cancelByAdmin(int $id, string $rejectionReason)
    {
        return DB::transaction(function () use ($id, $rejectionReason) {
            $request = CustomOrderRequest::query()
                ->with(['order.items', 'user'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($request->status->isFinal()) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.cannot_cancel', 422);
            }

            if (
                $request->order
                && $request->status === CustomOrderRequestStatus::WAITING_APPROVAL
            ) {
                $this->cancelLinkedOrder($request->order, $rejectionReason);
            }

            $request->update([
                'status' => CustomOrderRequestStatus::CANCELLED_BY_ADMIN->value,
                'rejection_reason' => $rejectionReason,
            ]);

            if ($request->user) {
                $this->notificationService->send(
                    $request->user,
                    'تم إلغاء طلبك المخصص',
                    "تم إلغاء طلبك المخصص #{$request->id}. السبب: {$rejectionReason}",
                    [
                        'type' => 'custom_order_request',
                        'custom_order_request_id' => (string) $request->id,
                        'status' => CustomOrderRequestStatus::CANCELLED_BY_ADMIN->value,
                    ]
                );
            }

            return new OneResource($request->fresh($this->relations));
        });
    }

    private function resolvePaymentMethod(CustomOrderRequest $request): PaymentMethod
    {
        if ($request->payment_method_id) {
            $method = PaymentMethod::query()->active()->find($request->payment_method_id);
            if ($method) {
                return $method;
            }
        }

        $default = PaymentMethod::resolveDefault();
        if (! $default) {
            throw new CustomExceptionWithMessage('custom.custom_order_requests.payment_method_unavailable', 422);
        }

        return $default;
    }

    private function buildOrderItemsPreview(array $items): array
    {
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $totalQuantity += $quantity;

            if (($item['type'] ?? '') === 'catalog') {
                $shopVariant = ShopProductVariant::with('productVariant.product')
                    ->findOrFail($item['shop_product_variant_id']);
                $unitPrice = (float) ($shopVariant->productVariant->price ?? 0);
                $subtotal += $unitPrice * $quantity;
            } else {
                $unitPrice = (float) $item['unit_price'];
                $subtotal += $unitPrice * $quantity;
            }
        }

        return [$subtotal, $totalQuantity];
    }

    private function persistOrderItems(Order $order, array $items): void
    {
        $inventory = app(InventoryService::class);

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];

            if (($item['type'] ?? '') === 'catalog') {
                $shopVariant = ShopProductVariant::with('productVariant.product')
                    ->findOrFail($item['shop_product_variant_id']);

                $productVariant = $shopVariant->productVariant;
                $product = $productVariant?->product;
                $unitPrice = (float) ($productVariant->price ?? 0);
                $lineTotal = $unitPrice * $quantity;

                $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'is_external' => false,
                    'product_name' => $product?->name ?? 'منتج',
                    'product_image' => $product?->image_url,
                    'variant_attributes' => $productVariant?->getAttributesValuesAttribute(),
                    'note' => $item['note'] ?? null,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'final_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                    'extras_total' => 0,
                    'total' => $lineTotal,
                    'item_status' => OrderStatus::WAITING_APPROVAL->value,
                ]);

                $inventory->decreaseStock($shopVariant->id, $quantity);
                continue;
            }

            $unitPrice = (float) $item['unit_price'];
            $lineTotal = $unitPrice * $quantity;
            $invoicePath = null;

            if (! empty($item['invoice_image'])) {
                $invoicePath = $item['invoice_image']->store('custom_order_invoices', 'public');
            }

            $order->items()->create([
                'shop_product_variant_id' => null,
                'is_external' => true,
                'product_name' => $item['product_name'],
                'product_image' => null,
                'invoice_image' => $invoicePath,
                'variant_attributes' => null,
                'note' => $item['note'] ?? 'مصدر خارجي',
                'quantity' => $quantity,
                'price' => $unitPrice,
                'unit_price' => $unitPrice,
                'final_price' => $unitPrice,
                'subtotal' => $lineTotal,
                'extras_total' => 0,
                'total' => $lineTotal,
                'item_status' => OrderStatus::WAITING_APPROVAL->value,
            ]);
        }
    }

    private function cancelLinkedOrder(Order $order, string $rejectionReason): void
    {
        $oldStatus = $order->status;
        $inventory = app(InventoryService::class);

        foreach ($order->items as $item) {
            if (! $item->is_external && $item->shop_product_variant_id) {
                $inventory->increaseStock((int) $item->shop_product_variant_id, (int) $item->quantity);
            }

            $item->update([
                'item_status' => OrderStatus::CANCELLED_BY_ADMIN->value,
            ]);
        }

        $order->update([
            'status' => OrderStatus::CANCELLED_BY_ADMIN->value,
            'rejection_reason' => $rejectionReason,
        ]);

        OrderStatusChanged::dispatch(
            $order->fresh('items'),
            $oldStatus,
            OrderStatus::CANCELLED_BY_ADMIN->value,
            'admin'
        );
    }

    private function notifyUserPriced(CustomOrderRequest $request, Order $order): void
    {
        if (! $request->user) {
            return;
        }

        $itemNames = $order->items->map(function ($item) {
            return "{$item->product_name} × {$item->quantity}";
        })->implode('، ');

        $body = "تم تجهيز طلبك: {$itemNames}. المجموع: {$order->total}.";

        if ($order->has_external_items) {
            $varianceText = $this->formatVariance($order->price_variance_type, $order->price_variance_value);
            $body .= " البنود الخارجية تقديرية وقد تختلف بحدود {$varianceText} حسب التوفر في السوق.";
        }

        $this->notificationService->send(
            $request->user,
            'تم تسعير طلبك — بانتظار موافقتك',
            $body,
            [
                'type' => 'custom_order_request',
                'custom_order_request_id' => (string) $request->id,
                'order_id' => (string) $order->id,
                'status' => CustomOrderRequestStatus::WAITING_APPROVAL->value,
            ]
        );
    }

    private function formatVariance(?string $type, $value): string
    {
        if ($type === PriceVarianceType::PERCENT->value) {
            return "±{$value}%";
        }

        if ($type === PriceVarianceType::FIXED->value) {
            return "±{$value}";
        }

        return (string) $value;
    }
}
