<?php

namespace App\Filament\Widgets;

use App\Models\VendorNotification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class VendorNotificationsWidget extends Widget
{
    protected string $view = 'filament.widgets.vendor-notifications-widget';

    protected static ?int $sort = -1;

    public function getNotifications()
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return collect();
        }

        return VendorNotification::where('vendor_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function markAsRead($notificationId)
    {
        $notification = VendorNotification::find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }

        $this->dispatch('refreshNotifications');
    }

    public function markAllAsRead()
    {
        $user = Auth::guard('vendor-user')->user();

        if ($user) {
            VendorNotification::where('vendor_user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $this->dispatch('refreshNotifications');
    }

    public function getUnreadCount()
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return 0;
        }

        return VendorNotification::where('vendor_user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getNotificationTypeLabel($type)
    {
        return match($type) {
            'order_status_changed' => 'تحديث الطلب',
            'new_order' => 'طلب جديد',
            'subscription_activated' => 'تفعيل الباقة',
            'subscription_expiring_soon' => 'انتهاء الباقة',
            'promotion_approved' => 'قبول الترويج',
            'promotion_rejected' => 'رفض الترويج',
            'promotion_created' => 'ترويج جديد',
            'product_approved' => 'قبول المنتج',
            'product_rejected' => 'رفض المنتج',
            'product_created' => 'منتج جديد',
            'low_stock' => 'مخزون منخفض',
            'subscription_error' => 'خطأ الاشتراك',
            default => $type,
        };
    }

    public function getNotificationBadgeColor($type)
    {
        return match($type) {
            'order_status_changed' => 'bg-blue-100 text-blue-800',
            'new_order' => 'bg-green-100 text-green-800',
            'subscription_activated' => 'bg-green-100 text-green-800',
            'subscription_expiring_soon' => 'bg-yellow-100 text-yellow-800',
            'promotion_approved' => 'bg-green-100 text-green-800',
            'promotion_rejected' => 'bg-red-100 text-red-800',
            'promotion_created' => 'bg-blue-100 text-blue-800',
            'product_approved' => 'bg-green-100 text-green-800',
            'product_rejected' => 'bg-red-100 text-red-800',
            'product_created' => 'bg-blue-100 text-blue-800',
            'low_stock' => 'bg-orange-100 text-orange-800',
            'subscription_error' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
