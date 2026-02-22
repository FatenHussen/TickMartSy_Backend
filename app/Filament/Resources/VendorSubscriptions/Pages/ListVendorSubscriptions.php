<?php

namespace App\Filament\Resources\VendorSubscriptions\Pages;

use App\Filament\Resources\VendorSubscriptions\VendorSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorSubscriptions extends ListRecords
{
    protected static string $resource = VendorSubscriptionResource::class;

    public function getTitle(): string
    {
        return 'اشتراكاتي';
    }

    public function getSubheading(): ?string
    {
        return 'عرض حالة اشتراكك الحالي والاشتراكات السابقة';
    }
}
