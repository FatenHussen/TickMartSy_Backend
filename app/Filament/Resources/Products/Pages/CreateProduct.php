<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected $variantMediaMap = [];
    protected $mainImagePath = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if ($user) {
            $data['vendor_id'] = $user->shops()->first()?->vendor_id;
        }

        Log::info('=== mutateFormDataBeforeCreate ===');
        Log::info('main_image in data:', ['main_image' => $data['main_image'] ?? 'NOT SET']);
        Log::info('media in data:', ['media' => $data['media'] ?? 'NOT SET']);

        // Store main_image before Filament processes it
        if (isset($data['main_image'])) {
            $this->mainImagePath = $data['main_image'];
            Log::info('Stored main_image:', ['path' => $this->mainImagePath]);
        }

        // Extract and store variant_media before Filament processes the data
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $key => $variantData) {
                if (isset($variantData['variant_media'])) {
                    $this->variantMediaMap[$key] = $variantData['variant_media'];
                }
            }
        }

        Log::info('variantMediaMap in mutate:', $this->variantMediaMap);

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
        if (isset($formData['media']) && is_array($formData['media'])) {
            foreach ($formData['media'] as $index => $filePath) {
                $product->media()->create([
                    'path' => $filePath,
                    'collection' => 'product',
                    'order' => $index,
                ]);
            }
            Log::info('Product media saved:', ['count' => count($formData['media'])]);
        }

        // Handle variant media using the stored map
        if (!empty($this->variantMediaMap)) {
            $product->load('variants');
            Log::info('Variants in DB:', $product->variants->count());
            Log::info('variantMediaMap:', $this->variantMediaMap);

            $variantIndex = 0;
            foreach ($product->variants as $variant) {
                // Try to find media for this variant by index
                if (isset($this->variantMediaMap[$variantIndex])) {
                    $mediaPaths = $this->variantMediaMap[$variantIndex];
                    Log::info("Saving media for variant {$variant->id} (index $variantIndex)");

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

        Log::info('=== afterCreate END ===');
    }
}
