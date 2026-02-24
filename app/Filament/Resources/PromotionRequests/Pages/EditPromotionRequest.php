<?php

namespace App\Filament\Resources\PromotionRequests\Pages;

use App\Filament\Resources\PromotionRequests\PromotionRequestResource;
use App\Enums\PromotionStatus;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionRequest extends EditRecord
{
    protected static string $resource = PromotionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn($record) => $record->status === PromotionStatus::PENDING || $record->status === PromotionStatus::REJECTED),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Reset status to pending when editing
        $data['status'] = PromotionStatus::PENDING->value;
        $data['admin_notes'] = null;
        $data['approved_at'] = null;
        $data['approved_by'] = null;

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث طلب الترويج بنجاح';
    }
}

