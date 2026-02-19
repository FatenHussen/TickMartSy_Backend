<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected $variantMediaMap = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if ($user) {
            $data['vendor_id'] = $user->shops()->first()?->vendor_id;
        }

        // Extract and store variant_media before Filament processes the data
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $key => $variantData) {
                if (isset($variantData['variant_media'])) {
                    $this->variantMediaMap[$key] = $variantData['variant_media'];
                }
            }
        }

        \Log::info('variantMediaMap in mutate:', $this->variantMediaMap);

        return $data;
    }

    protected function afterCreate(): void
    {
        \Log::info('=== afterCreate START ===');
        
        $product = $this->record;
        $formData = $this->form->getState();

        // Handle product media
        if (isset($formData['media']) && is_array($formData['media'])) {
            foreach ($formData['media'] as $index => $filePath) {
                $product->media()->create([
                    'path' => $filePath,
                    'collection' => 'product',
                    'order' => $index,
                ]);
            }
        }

        // Handle variant media using the stored map
        if (!empty($this->variantMediaMap)) {
            $product->load('variants');
            \Log::info('Variants in DB:', $product->variants->count());
            \Log::info('variantMediaMap:', $this->variantMediaMap);
            
            $variantIndex = 0;
            foreach ($product->variants as $variant) {
                // Try to find media for this variant by index
                if (isset($this->variantMediaMap[$variantIndex])) {
                    $mediaPaths = $this->variantMediaMap[$variantIndex];
                    \Log::info("Saving media for variant {$variant->id} (index $variantIndex)");
                    
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
        
        \Log::info('=== afterCreate END ===');
    }
}
