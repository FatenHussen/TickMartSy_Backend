<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\Base\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $mediaService = new MediaService();
        
        // Load existing product media
        $product = $this->record;
        $data['media'] = $mediaService->getCollection($product, 'product')->pluck('path')->toArray();

        // Load variant media
        if (isset($data['variants'])) {
            foreach ($data['variants'] as $index => $variant) {
                if (isset($variant['id'])) {
                    $variantModel = $product->variants()->find($variant['id']);
                    if ($variantModel) {
                        $data['variants'][$index]['variant_media'] = $mediaService
                            ->getCollection($variantModel, 'product-variant')
                            ->pluck('path')
                            ->toArray();
                    }
                }
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $mediaService = new MediaService();
        
        // Store product media files
        $productMediaFiles = [];
        if (isset($data['media'])) {
            $productMediaFiles = $data['media'];
            unset($data['media']);
        }

        // Store variant media before updating the record
        $variantMediaData = [];
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $index => $variantData) {
                if (isset($variantData['variant_media'])) {
                    // Use variant ID as key if exists
                    $key = $variantData['id'] ?? $index;
                    $variantMediaData[$key] = $variantData['variant_media'];
                    unset($data['variants'][$index]['variant_media']);
                }
            }
        }

        // Update the product
        $product = parent::handleRecordUpdate($record, $data);

        // Sync product media
        $mediaService->deleteByCollection($product, 'product');
        if (!empty($productMediaFiles) && is_array($productMediaFiles)) {
            foreach ($productMediaFiles as $filePath) {
                $product->media()->create([
                    'path' => $filePath,
                    'collection' => 'product',
                    'order' => $product->media()->where('collection', 'product')->count(),
                ]);
            }
        }

        // Sync variant media after variants are updated
        if (!empty($variantMediaData)) {
            foreach ($variantMediaData as $variantId => $mediaPaths) {
                $variant = $product->variants()->find($variantId);
                
                if ($variant) {
                    // Delete old variant media
                    $mediaService->deleteByCollection($variant, 'product-variant');

                    // Add new variant media
                    if (is_array($mediaPaths)) {
                        foreach ($mediaPaths as $filePath) {
                            $variant->media()->create([
                                'path' => $filePath,
                                'collection' => 'product-variant',
                                'order' => $variant->media()->where('collection', 'product-variant')->count(),
                            ]);
                        }
                    }
                }
            }
        }

        return $product;
    }
}
