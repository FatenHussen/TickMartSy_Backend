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

    protected function afterCreate(): void
    {
        $product = $this->record;
        $data = $this->form->getState();

        // Handle product media
        if (isset($data['media']) && is_array($data['media'])) {
            foreach ($data['media'] as $index => $mediaPath) {
                $product->media()->create([
                    'path' => $mediaPath,
                    'collection' => 'product',
                    'order' => $index + 1,
                ]);
            }
        }

        // Handle variant media - need to reload variants after creation
        if (isset($data['variants']) && is_array($data['variants'])) {
            $product->load('variants');

            foreach ($product->variants as $variantIndex => $variant) {
                if (isset($data['variants'][$variantIndex]['variant_media']) && is_array($data['variants'][$variantIndex]['variant_media'])) {
                    foreach ($data['variants'][$variantIndex]['variant_media'] as $index => $mediaPath) {
                        $variant->media()->create([
                            'path' => $mediaPath,
                            'collection' => 'product-variant',
                            'order' => $index + 1,
                        ]);
                    }
                }
            }
        }
    }
}
