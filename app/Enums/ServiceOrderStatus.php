<?php

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case PENDING = 'pending';
    case CANCELED = 'canceled';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';

    public function labelAr(): string
    {
        return match ($this) {
            self::PENDING => 'قائل الانتظار',
            self::CANCELED => 'ملغي',
            self::REJECTED => 'مر�?وق',
            self::COMPLETED => 'متما',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CANCELED => 'Canceled',
            self::REJECTED => 'Rejected',
            self::COMPLETED => 'Completed',
        };
    }
}
