<?php

namespace App\Services\Vendor;

use App\Models\VendorNotification;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Log;

class VendorNotificationService
{
    /**
     * Send notification to vendor users
     */
    public function notifyVendor(
        ?int $vendorId,
        string $title,
        string $body,
        string $type,
        array $data = []
    ): void {
        if (!$vendorId) {
            return;
        }

        try {
            // Get all vendor users
            $vendorUsers = VendorUser::where('vendor_id', $vendorId)->get();

            foreach ($vendorUsers as $user) {
                VendorNotification::create([
                    'vendor_user_id' => $user->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => $type,
                    'data' => array_merge($data, ['format' => 'filament']),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send vendor notification', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify vendor about order status change
     */
    public function notifyOrderStatusChanged(
        $order,
        string $fromStatus,
        string $toStatus
    ): void {
        $vendor = $order->shop?->vendor;
        if (!$vendor) {
            return;
        }

        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'preparing' => 'قيد التحضير',
            'out_delivery' => 'خرج للتوصيل',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
        ];

        $toLabel = $statusLabels[$toStatus] ?? $toStatus;

        $this->notifyVendor(
            $vendor->id,
            'تحديث حالة الطلب',
            "تم تحديث حالة الطلب #{$order->order_code} إلى: {$toLabel}",
            'order_status_changed',
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ]
        );
    }

    /**
     * Notify vendor about new order
     */
    public function notifyNewOrder($order): void
    {
        $vendor = $order->shop?->vendor;
        if (!$vendor) {
            return;
        }

        $this->notifyVendor(
            $vendor->id,
            'طلب جديد',
            "تم استقبال طلب جديد #{$order->order_code} بقيمة {$order->total}",
            'new_order',
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'total' => $order->total,
            ]
        );
    }

    /**
     * Notify vendor about subscription activation
     */
    public function notifySubscriptionActivated($subscription): void
    {
        $package = $subscription->package;

        $this->notifyVendor(
            $subscription->vendor_id,
            'تم تفعيل الباقة',
            "تم تفعيل باقة {$package->name} حتى {$subscription->ends_at->format('Y-m-d')}",
            'subscription_activated',
            [
                'subscription_id' => $subscription->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'ends_at' => $subscription->ends_at->toDateString(),
            ]
        );
    }

    /**
     * Notify vendor about subscription expiring soon
     */
    public function notifySubscriptionExpiringSoon($subscription, int $daysLeft): void
    {
        $package = $subscription->package;

        $this->notifyVendor(
            $subscription->vendor_id,
            'الباقة ستنتهي قريباً',
            "باقة {$package->name} ستنتهي خلال {$daysLeft} أيام",
            'subscription_expiring_soon',
            [
                'subscription_id' => $subscription->id,
                'package_id' => $package->id,
                'days_left' => $daysLeft,
            ]
        );
    }

    /**
     * Notify vendor about promotion request approval
     */
    public function notifyPromotionApproved($promotionRequest): void
    {
        $this->notifyVendor(
            $promotionRequest->vendor_id,
            'تم قبول طلب الترويج',
            "تم قبول طلب الترويج: {$promotionRequest->title}",
            'promotion_approved',
            [
                'promotion_request_id' => $promotionRequest->id,
                'title' => $promotionRequest->title,
            ]
        );
    }

    /**
     * Notify vendor about promotion request rejection
     */
    public function notifyPromotionRejected($promotionRequest): void
    {
        $this->notifyVendor(
            $promotionRequest->vendor_id,
            'تم رفض طلب الترويج',
            "تم رفض طلب الترويج: {$promotionRequest->title}. السبب: {$promotionRequest->admin_notes}",
            'promotion_rejected',
            [
                'promotion_request_id' => $promotionRequest->id,
                'title' => $promotionRequest->title,
                'reason' => $promotionRequest->admin_notes,
            ]
        );
    }

    /**
     * Notify vendor about product approval
     */
    public function notifyProductApproved($product): void
    {
        $this->notifyVendor(
            $product->vendor_id,
            'تم قبول المنتج',
            "تم قبول المنتج: {$product->name}",
            'product_approved',
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
            ]
        );
    }

    /**
     * Notify vendor about product rejection
     */
    public function notifyProductRejected($product, string $reason = ''): void
    {
        $this->notifyVendor(
            $product->vendor_id,
            'تم رفض المنتج',
            "تم رفض المنتج: {$product->name}. السبب: {$reason}",
            'product_rejected',
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Notify vendor about low stock
     */
    public function notifyLowStock($product, int $currentStock): void
    {
        $this->notifyVendor(
            $product->vendor_id,
            'تنبيه: المخزون منخفض',
            "المنتج {$product->name} المخزون المتبقي: {$currentStock} وحدة",
            'low_stock',
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $currentStock,
            ]
        );
    }
}

