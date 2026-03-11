<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\Vendor\VendorSubscriptionQuotaService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected $variantMediaBySignature = [];
    protected $variantMediaByIndex = [];
    protected $productMediaPaths = [];
    protected $mainImagePath = null;

    public function mount(): void
    {
        parent::mount();

        $this->guardSubscription();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        // Check subscription status before creating
        $quota = app(VendorSubscriptionQuotaService::class)->getUsageSnapshot($user);

        if (!$quota['has_active']) {
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_no_active_title'),
                __('custom.subscription_no_active_body'),
                'subscription_error'
            );

            $this->halt();
        }

        if (!$quota['can_create_product']) {
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_limit_products_title'),
                __('custom.subscription_limit_products_body'),
                'subscription_error'
            );

            $this->halt();
        }

        if ($user && empty($data['vendor_id'])) {
            $data['vendor_id'] = $user->vendor_id ?? $user->shops()->first()?->vendor_id;
        }

        Log::info('=== mutateFormDataBeforeCreate ===');
        Log::info('main_image in data:', ['main_image' => $data['main_image'] ?? 'NOT SET']);
        Log::info('media in data:', ['media' => $data['media'] ?? 'NOT SET']);

        $formState = method_exists($this->form, 'getRawState')
            ? $this->form->getRawState()
            : $this->form->getState();

        // Store main_image before Filament processes it
        if (isset($formState['main_image'])) {
            $mainImages = $this->normalizeUploadState($formState['main_image']);
            $this->mainImagePath = $mainImages[0] ?? null;
            Log::info('Stored main_image:', ['path' => $this->mainImagePath]);
        }

        // Store product media before Filament processes it
        if (isset($formState['media']) && is_array($formState['media'])) {
            $this->productMediaPaths = $this->normalizeUploadState($formState['media']);
        }

        // Extract and store variant_media before Filament processes the data
        if (isset($formState['variants']) && is_array($formState['variants'])) {
            $variantIndex = 0;
            foreach ($formState['variants'] as $variantData) {
                if (isset($variantData['variant_media'])) {
                    $signature = $this->buildVariantSignature(
                        $variantData['attributes_values_ids'] ?? null
                    );

                    $variantMedia = $this->normalizeUploadState($variantData['variant_media']);

                    if ($signature) {
                        $this->variantMediaBySignature[$signature] = $variantMedia;
                    } else {
                        $this->variantMediaByIndex[$variantIndex] = $variantMedia;
                    }
                }
                $variantIndex++;
            }
        }

        Log::info('variantMediaBySignature in mutate:', $this->variantMediaBySignature);
        Log::info('variantMediaByIndex in mutate:', $this->variantMediaByIndex);

        return $data;
    }

    protected function afterCreate(): void
    {
        Log::info('=== afterCreate START ===');

        $product = $this->record;
        $formData = $this->form->getState();

        Log::info('mainImagePath property:', ['path' => $this->mainImagePath]);
        Log::info('main_image in formData:', ['main_image' => $formData['main_image'] ?? 'NOT SET']);

        // Handle main image from stored property
        if ($this->mainImagePath) {
            $product->media()->create([
                'path' => $this->mainImagePath,
                'collection' => 'main',
                'order' => 0,
            ]);
            Log::info('Main image saved from property:', ['path' => $this->mainImagePath]);
        } elseif (isset($formData['main_image']) && !empty($formData['main_image'])) {
            // Fallback: try from formData
            $product->media()->create([
                'path' => $formData['main_image'],
                'collection' => 'main',
                'order' => 0,
            ]);
            Log::info('Main image saved from formData:', ['path' => $formData['main_image']]);
        } else {
            Log::warning('No main_image found in property or formData');
        }

        // Handle product media (additional images)
        $mediaPaths = $this->productMediaPaths;
        if (empty($mediaPaths) && isset($formData['media']) && is_array($formData['media'])) {
            $mediaPaths = $formData['media'];
        }
        if (!empty($mediaPaths)) {
            foreach ($mediaPaths as $index => $filePath) {
                $product->media()->create([
                    'path' => $filePath,
                    'collection' => 'product',
                    'order' => $index,
                ]);
            }
            Log::info('Product media saved:', ['count' => count($mediaPaths)]);
        }

        // Handle variant media using stored maps
        if (!empty($this->variantMediaBySignature) || !empty($this->variantMediaByIndex)) {
            $product->load('variants');
            Log::info('Variants in DB:', ['count' => $product->variants->count()]);
            Log::info('variantMediaBySignature:', $this->variantMediaBySignature);
            Log::info('variantMediaByIndex:', $this->variantMediaByIndex);

            $variantIndex = 0;
            foreach ($product->variants as $variant) {
                $signature = $this->buildVariantSignature($variant->attributes_values_ids ?? null);
                $mediaPaths = null;

                if ($signature && isset($this->variantMediaBySignature[$signature])) {
                    $mediaPaths = $this->variantMediaBySignature[$signature];
                    Log::info("Saving media for variant {$variant->id} (signature $signature)");
                } elseif (isset($this->variantMediaByIndex[$variantIndex])) {
                    $mediaPaths = $this->variantMediaByIndex[$variantIndex];
                    Log::info("Saving media for variant {$variant->id} (index $variantIndex)");
                }

                if (is_array($mediaPaths)) {
                    foreach ($mediaPaths as $index => $filePath) {
                        $variant->media()->create([
                            'path' => $filePath,
                            'collection' => 'product-variant',
                            'order' => $index,
                        ]);
                    }
                }
                $variantIndex++;
            }
        }

        $this->notifyRemainingQuota();
        $this->notifyProductCreated();

        Log::info('=== afterCreate END ===');
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

        if (!$quota['can_create_product']) {
            $this->createAndNotifyVendor(
                $user,
                __('custom.subscription_limit_products_title'),
                __('custom.subscription_limit_products_body'),
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

        $remaining = $quota['remaining_products'];
        $remainingLabel = $remaining === null ? __('custom.unlimited') : (string) $remaining;

        Notification::make()
            ->title(__('custom.subscription_remaining_products_title'))
            ->body(__('custom.subscription_remaining_products_body', ['count' => $remainingLabel]))
            ->success()
            ->send();
    }

    private function buildVariantSignature($attributesValuesIds): ?string
    {
        if (!is_array($attributesValuesIds) || empty($attributesValuesIds)) {
            return null;
        }

        $values = array_values(array_filter($attributesValuesIds));
        sort($values);

        if (empty($values)) {
            return null;
        }

        return 'attr:' . implode(',', $values);
    }

    private function normalizeUploadState($state): array
    {
        if (is_string($state)) {
            return [$state];
        }

        if (!is_array($state)) {
            return [];
        }

        $paths = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($state));
        foreach ($iterator as $value) {
            if (is_string($value) && $value !== '') {
                $paths[] = $value;
            }
        }

        return array_values(array_unique($paths));
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

    private function notifyProductCreated(): void
    {
        /** @var \App\Models\VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();
        if (!$user) {
            return;
        }

        $notificationService = app(\App\Services\Vendor\VendorNotificationService::class);
        $notificationService->notifyVendor(
            $user->vendor_id,
            'تم إنشاء منتج جديد',
            "تم إنشاء منتج جديد: {$this->record->name}",
            'product_created',
            [
                'product_id' => $this->record->id,
                'product_name' => $this->record->name,
            ]
        );
    }
}
