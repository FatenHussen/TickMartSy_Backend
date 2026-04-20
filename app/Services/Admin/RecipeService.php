<?php

namespace App\Services\Admin;

use App\Http\Resources\Recipe\AdminOneResource;
use App\Http\Resources\Recipe\AllResource;
use App\Models\Recipe;
use App\Services\Base\MediaService;
use App\Services\BaseService;

class RecipeService extends BaseService
{
    public function __construct(Recipe $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['items', 'steps', 'media'];
        $this->searchableFields = ['id', 'name'];
        $this->syncRelations = [
            'items'   => 'items',
            'steps' => 'steps',
            'badges'   => 'badges',
        ];

        $this->mediaCollections = [
            'images' => [
                'collection' => 'recipe',
                'type' => 'multiple',
            ],
        ];

        $this->singleImages = [
            'image'  => 'image',
        ];
    }

    public function create($data)
    {
        $resource = parent::create($data);
        $this->syncPrimaryImageFromMedia($resource->resource);

        return new $this->resource($resource->resource->fresh());
    }

    public function update($id, array $data)
    {
        $resource = parent::update($id, $data);
        $this->syncPrimaryImageFromMedia($resource->resource);

        return new $this->resource($resource->resource->fresh());
    }

    protected function handleRelations($object, array &$data)
    {
        if (isset($data['existing_media_ids']) || isset($data['images'])) {
            $existingIds = $data['existing_media_ids'] ?? [];
            $newFiles = $data['images'] ?? [];
            $mediaService = new MediaService();

            $currentMedia = $object->media()->where('collection', 'recipe')->get();
            foreach ($currentMedia as $media) {
                if (!in_array($media->id, $existingIds)) {
                    $mediaService->delete($media);
                }
            }

            if (is_array($newFiles)) {
                $mediaService->uploadMultiple($object, $newFiles, 'recipe');
            }

            unset($data['existing_media_ids']);
        }

        parent::handleRelations($object, $data);
    }

    private function syncPrimaryImageFromMedia(Recipe $recipe): void
    {
        $primaryMediaPath = $recipe->media()->value('path');

        if (!empty($primaryMediaPath) && $recipe->image !== $primaryMediaPath) {
            $recipe->update(['image' => $primaryMediaPath]);
        }
    }
}
