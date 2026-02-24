<?php

namespace App\Filament\Resources\PromotionRequests\Pages;

use App\Filament\Resources\PromotionRequests\PromotionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionRequests extends ListRecords
{
    protected static string $resource = PromotionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('طلب ترويج جديد'),
        ];
    }
}
