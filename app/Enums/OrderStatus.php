<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PREPARING = 'preparing';
    case OUT_DELIVERY = 'out_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REJECTEDBYDELIVERY = 'rejected_by_delivery';
    case FAILDDELIVER = 'faild_deliver';

    public function labelAr(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::PREPARING => 'قيد التحضير',
            self::OUT_DELIVERY => 'خرج للتوصيل',
            self::DELIVERED => 'تم التوصيل',
            self::CANCELLED => 'ملغي',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PREPARING => 'Preparing',
            self::OUT_DELIVERY => 'Out for delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }
}
