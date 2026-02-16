<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if ($user) {
            $data['vendor_id'] = $user->shops()->first()?->vendor_id;
        }

        return $data;
    }
}
