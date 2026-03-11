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
        $quota = app(VendorSubscriptionQuotaService::class)->getUsageSnapshot($user);

        // Check subscription status
        if (!$quota['has_active']) {
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_no_active_title'),
                __('custom.subscription_no_active_body'),
                'subscription_error'
            );
            $this->halt();
        }

        // Check campaign quota
        if (!$quota['can_create_campaign']) {
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_limit_campaigns_title'),
                __('custom.subscription_limit_campaigns_body'),
                'subscription_error'
            );
            $this->halt();
        }

        $data['vendor_id'] = $user->vendor_id;
        $data['status'] = PromotionStatus::PENDING->value;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->notifyRemainingQuota();
        $this->notifyPromotionCreated();
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
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_no_active_title'),
                __('custom.subscription_no_active_body'),
                'subscription_error'
            );

            $this->redirect($this->getResource()::getUrl('index'), navigate: true);
            return;
        }

        if (!$quota['can_create_campaign']) {
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_limit_campaigns_title'),
                __('custom.subscription_limit_campaigns_body'),
                'subscription_error'
            );

            $this->redirect($this->getResource()::getUrl('index'), navigate: true);
            return;
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

    private function notifyPromotionCreated(): void
    {
        $user = Auth::guard('vendor-user')->user();
        if (!$user) {
            return;
        }

        $notificationService = app(\App\Services\Vendor\VendorNotificationService::class);
        $notificationService->notifyVendor(
            $user->vendor_id,
            'تم إنشاء طلب ترويج جديد',
            "تم إنشاء طلب ترويج جديد: {$this->record->title}",
            'promotion_created',
            [
                'promotion_request_id' => $this->record->id,
                'title' => $this->record->title,
            ]
        );
    }

    private function createAndNotifyVendor($user, $title, $body, $type): void
    {
        if (!$user) {
            return;
        }

        // Create database notification
        \App\Models\VendorNotification::create([
            'vendor_user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => [
                'format' => 'filament',
            ],
        ]);

        // Also show Filament notification for immediate feedback
        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->persistent()
            ->send();
    }
}
