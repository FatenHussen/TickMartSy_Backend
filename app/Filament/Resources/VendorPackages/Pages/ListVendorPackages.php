<?php

namespace App\Filament\Resources\VendorPackages\Pages;

use App\Filament\Resources\VendorPackages\VendorPackageResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorPackages extends ListRecords
{
    protected static string $resource = VendorPackageResource::class;

    public function getTitle(): string
    {
        return 'الباقات المتاحة';
    }

    public function getSubheading(): ?string
    {
        return 'اختر الباقة المناسبة لك واشترك للاستفادة من المزايا';
    }
}
