<?php

namespace App\Enums;

enum CustomOrderRequestStatus: string
{
    case PENDING_PRICING = 'pending_pricing';
    case WAITING_APPROVAL = 'waiting_approval';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
    case CANCELLED_BY_ADMIN = 'cancelled_by_admin';

    public function labelAr(): string
    {
        return match ($this) {
            self::PENDING_PRICING => 'بانتظار التسعير',
            self::WAITING_APPROVAL => 'بانتظار موافقة الزبون',
            self::APPROVED => 'تمت الموافقة',
            self::CANCELLED => 'ملغي',
            self::CANCELLED_BY_ADMIN => 'ملغي من الإدارة',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::PENDING_PRICING => 'Pending pricing',
            self::WAITING_APPROVAL => 'Waiting approval',
            self::APPROVED => 'Approved',
            self::CANCELLED => 'Cancelled',
            self::CANCELLED_BY_ADMIN => 'Cancelled by admin',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::CANCELLED,
            self::CANCELLED_BY_ADMIN,
        ], true);
    }
}
