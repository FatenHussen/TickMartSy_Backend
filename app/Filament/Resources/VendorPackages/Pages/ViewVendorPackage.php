<?php

namespace App\Filament\Resources\VendorPackages\Pages;

use App\Filament\Resources\VendorPackages\VendorPackageResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVendorPackage extends ViewRecord
{
    protected static string $resource = VendorPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit action - vendors can only view packages
        ];
    }
}

