<?php

namespace App\Filament\Resources\PromotionRequests\Pages;

use App\Enums\PromotionStatus;
use App\Filament\Resources\PromotionRequests\PromotionRequestResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePromotionRequest extends CreateRecord
{
    protected static string $resource = PromotionRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::guard('vendor-user')->user();

        $data['vendor_id'] = $user->vendor_id;
        $data['status'] = PromotionStatus::PENDING->value;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إرسال طلب الترويج بنجاح';
    }
}

