<?php

namespace App\Filament\Resources\PromotionRequests\Pages;

use App\Filament\Resources\PromotionRequests\PromotionRequestResource;
use App\Enums\PromotionStatus;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPromotionRequest extends ViewRecord
{
    protected static string $resource = PromotionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn($record) => $record->status === PromotionStatus::PENDING || $record->status === PromotionStatus::REJECTED),
            Actions\DeleteAction::make()
                ->visible(fn($record) => $record->status === PromotionStatus::PENDING || $record->status === PromotionStatus::REJECTED),
        ];
    }
}
