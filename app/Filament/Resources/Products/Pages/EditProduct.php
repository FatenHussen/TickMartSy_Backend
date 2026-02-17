<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
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
        // Load existing media
        $product = $this->record;
        $data['media'] = $product->media->pluck('path')->toArray();

        // Load variant media
        if (isset($data['variants'])) {
            foreach ($data['variants'] as $index => $variant) {
                if (isset($variant['id'])) {
                    $variantModel = $product->variants()->find($variant['id']);
                    if ($variantModel) {
                        $data['variants'][$index]['variant_media'] = $variantModel->media->pluck('path')->toArray();
                    }
                }
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $product = $this->record;
        $data = $this->form->getState();

        // Sync product media
        if (isset($data['media'])) {
            // Delete old media
            $product->media()->where('collection', 'product')->delete();

            // Add new media
            if (is_array($data['media'])) {
                foreach ($data['media'] as $index => $mediaPath) {
                    $product->media()->create([
                        'path' => $mediaPath,
                        'collection' => 'product',
                        'order' => $index + 1,
                    ]);
                }
            }
        }

        // Sync variant media
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
                if (isset($variantData['id'])) {
                    $variant = $product->variants()->find($variantData['id']);
                    if ($variant) {
                        // Delete old variant media
                        $variant->media()->where('collection', 'product-variant')->delete();

                        // Add new variant media
                        if (isset($variantData['variant_media']) && is_array($variantData['variant_media'])) {
                            foreach ($variantData['variant_media'] as $index => $mediaPath) {
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
    }
}
