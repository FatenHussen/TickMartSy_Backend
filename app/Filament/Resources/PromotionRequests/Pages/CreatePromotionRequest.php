<?php

namespace App\Filament\Resources\PromotionRequests\Pages;

use App\Enums\PromotionStatus;
use App\Filament\Resources\PromotionRequests\PromotionRequestResource;
use App\Services\Vendor\VendorSubscriptionQuotaService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePromotionRequest extends CreateRecord
{
    protected static string $resource = PromotionRequestResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->guardSubscription();
    }

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

    protected function afterCreate(): void
    {
        $this->notifyRemainingQuota();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إرسال طلب الترويج بنجاح';
    }

    private function guardSubscription(): void
    {
        $user = Auth::guard('vendor-user')->user();
        $quota = app(VendorSubscriptionQuotaService::class)->getUsageSnapshot($user);

        if (!$quota['has_active']) {
            Notification::make()
                ->title(__('custom.subscription_no_active_title'))
                ->body(__('custom.subscription_no_active_body'))
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        if (!$quota['can_create_campaign']) {
            Notification::make()
                ->title(__('custom.subscription_limit_campaigns_title'))
                ->body(__('custom.subscription_limit_campaigns_body'))
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    private function notifyRemainingQuota(): void
    {
        $user = Auth::guard('vendor-user')->user();
        $quota = app(VendorSubscriptionQuotaService::class)->getUsageSnapshot($user);

        if (!$quota['has_active']) {
            return;
        }

        $remaining = $quota['remaining_campaigns'];
        $remainingLabel = $remaining === null ? __('custom.unlimited') : (string) $remaining;

        Notification::make()
            ->title(__('custom.subscription_remaining_campaigns_title'))
            ->body(__('custom.subscription_remaining_campaigns_body', ['count' => $remainingLabel]))
            ->success()
            ->send();
    }
}
