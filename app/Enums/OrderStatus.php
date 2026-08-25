<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case WAITING_APPROVAL = 'waiting_approval';
    case PREPARING = 'preparing';
    case OUT_DELIVERY = 'out_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case CANCELLED_BY_ADMIN = 'cancelled_by_admin';
    case REJECTEDBYDELIVERY = 'rejected_by_delivery';
    case FAILDDELIVER = 'faild_deliver';
    case RETURNED_BY_USER = 'returned_by_user';

    public function labelAr(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::WAITING_APPROVAL => 'بانتظار موافقة الزبون',
            self::PREPARING => 'قيد التحضير',
            self::OUT_DELIVERY => 'خرج للتوصيل',
            self::DELIVERED => 'تم التوصيل',
            self::CANCELLED => 'ملغي',
            self::CANCELLED_BY_ADMIN => 'ملغي من الإدارة',
            self::REJECTEDBYDELIVERY => 'مرفوض من الدليفري',
            self::FAILDDELIVER => 'فشل التوصيل',
            self::RETURNED_BY_USER => 'مرتجع من قبل المستخدم',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::WAITING_APPROVAL => 'Waiting approval',
            self::PREPARING => 'Preparing',
            self::OUT_DELIVERY => 'Out for delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::CANCELLED_BY_ADMIN => 'Cancelled by admin',
            self::REJECTEDBYDELIVERY => 'Rejected by delivery',
            self::FAILDDELIVER => 'Failed delivery',
            self::RETURNED_BY_USER => 'Returned by user',
        };
    }
}
