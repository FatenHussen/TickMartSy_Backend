<?php

namespace App\Filament\Resources\StoreUsers\Pages;

use App\Filament\Resources\StoreUsers\StoreUserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStoreUser extends ViewRecord
{
    protected static string $resource = StoreUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
