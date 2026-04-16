<?php

namespace App\Filament\Resources\VendorInventories\Pages;

use App\Filament\Resources\VendorInventories\VendorInventoryResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorInventories extends ListRecords
{
    protected static string $resource = VendorInventoryResource::class;
}
