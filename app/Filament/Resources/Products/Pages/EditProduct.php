<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\Base\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

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

        // Load main image
        $mainImage = $mediaService->getCollection($product, 'main')->first();
        if (!$mainImage) {
            $mainImage = $mediaService->getCollection($product, 'product')->first();
        }
        if ($mainImage) {
            $data['main_image'] = $mainImage->path;
        }

        // Load additional images
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

        if ($this->record && $this->record->relationLoaded('badges') === false) {
            $this->record->load('badges');
        }

        $topBadge = $this->record->badges
            ->first(fn ($badge) => ($badge->pivot->position ?? null) === 'top');

        $bottomBadgeIds = $this->record->badges
            ->filter(fn ($badge) => ($badge->pivot->position ?? null) === 'bottom')
            ->pluck('id')
            ->values()
            ->all();

        $data['top_badge_id'] = $topBadge?->id;
        $data['bottom_badge_ids'] = $bottomBadgeIds;

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $mediaService = new MediaService();

        $topBadgeId = !empty($data['top_badge_id']) ? (int) $data['top_badge_id'] : null;
        $bottomBadgeIds = collect($data['bottom_badge_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        unset($data['top_badge_id'], $data['bottom_badge_ids']);

        // Store main image
        $mainImageFile = null;
        $hasMainImage = array_key_exists('main_image', $data);
        if ($hasMainImage) {
            $mainImageFile = $data['main_image'];
            unset($data['main_image']);
        }

        // Store product media files
        $productMediaFiles = [];
        $hasProductMedia = array_key_exists('media', $data);
        if ($hasProductMedia) {
            $productMediaFiles = $data['media'];
            unset($data['media']);
        }

        // Store variant media before updating the record
        $variantMediaById = [];
        $variantMediaBySignature = [];
        $variantMediaByIndex = [];
        if (isset($data['variants']) && \is_array($data['variants'])) {
            foreach ($data['variants'] as $index => $variantData) {
                if (isset($variantData['variant_media'])) {
                    $signature = $this->buildVariantSignature($variantData['attributes_values_ids'] ?? null);
                    if ($signature) {
                        $variantMediaBySignature[$signature] = $variantData['variant_media'];
                    } elseif (!empty($variantData['id'])) {
                        $variantMediaById[$variantData['id']] = $variantData['variant_media'];
                    } else {
                        $variantMediaByIndex[$index] = $variantData['variant_media'];
                    }
                    unset($data['variants'][$index]['variant_media']);
                }
            }
        }

        // Update the product
        $product = parent::handleRecordUpdate($record, $data);

        // Sync main image
        if ($hasMainImage) {
            $this->syncMediaCollection($product, 'main', $mainImageFile ? [$mainImageFile] : [], $mediaService);
        }

        // Sync product media
        if ($hasProductMedia && \is_array($productMediaFiles)) {
            $this->syncMediaCollection($product, 'product', $productMediaFiles, $mediaService);
        }

        // Sync variant media after variants are updated
        if (!empty($variantMediaById) || !empty($variantMediaBySignature) || !empty($variantMediaByIndex)) {
            $product->load('variants');
            $variantIndex = 0;
            foreach ($product->variants as $variant) {
                $mediaPaths = null;
                $signature = $this->buildVariantSignature($variant->attributes_values_ids ?? null);

                if ($signature && isset($variantMediaBySignature[$signature])) {
                    $mediaPaths = $variantMediaBySignature[$signature];
                } elseif (isset($variantMediaById[$variant->id])) {
                    $mediaPaths = $variantMediaById[$variant->id];
                } elseif (isset($variantMediaByIndex[$variantIndex])) {
                    $mediaPaths = $variantMediaByIndex[$variantIndex];
                }

                if (\is_array($mediaPaths)) {
                    $this->syncMediaCollection($variant, 'product-variant', $mediaPaths, $mediaService);
                }
                $variantIndex++;
            }
        }

        $this->syncBadges($product, $topBadgeId, $bottomBadgeIds);

        return $product;
    }

    private function syncBadges(Model $product, ?int $topBadgeId, array $bottomBadgeIds): void
    {
        $sync = [];

        if ($topBadgeId) {
            $sync[$topBadgeId] = ['position' => 'top'];
        }

        foreach ($bottomBadgeIds as $badgeId) {
            if ($topBadgeId && $badgeId === $topBadgeId) {
                continue;
            }

            $sync[$badgeId] = ['position' => 'bottom'];
        }

        $product->badges()->sync($sync);
    }

    private function syncMediaCollection(
        Model $model,
        string $collection,
        array $paths,
        MediaService $mediaService
    ): void {
        $paths = collect($paths)
            ->filter(fn($path) => is_string($path) && $path !== '')
            ->values();

        $existing = $model->media()
            ->where('collection', $collection)
            ->get();

        $existingByPath = $existing->keyBy('path');

        // Delete removed media (and their files)
        foreach ($existing as $media) {
            if (!$paths->contains($media->path)) {
                $mediaService->delete($media);
            }
        }

        // Create or reorder kept media
        foreach ($paths as $index => $path) {
            $media = $existingByPath->get($path);
            if ($media) {
                if ($media->order !== $index) {
                    $media->update(['order' => $index]);
                }
                continue;
            }

            $model->media()->create([
                'path' => $path,
                'collection' => $collection,
                'order' => $index,
            ]);
        }
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
}
