<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Traits\FileTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    use FileTrait;

    /* ================= Core ================= */

    protected $model;
    protected $resource;
    protected $collection;
    protected $relations = [];
    protected $pagination = true;
    protected $syncRelations = [];
    protected $mediaCollections = [];
    protected $searchableFields = ['id'];
    protected $sortableFields   = ['id'];
    protected $imageColumn;
    protected $imageFolder;
    protected $imagesFolder;

    protected $imagesRelation;

    public function getAll($filters = [], $config = [])
    {
        $query = $this->model::query()->with($this->relations);
        $query = $this->queryBuilder($query, $filters, $config);

        if ($this->pagination) {
            $perPage = $config['per_page'] ?? 10;
            $page    = $config['page'] ?? 1;
            $result = $query->paginate($perPage, ['*'], 'page', $page);

            return [
                'items' => ($this->collection)::collection($result->items()),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'last_page'    => $result->lastPage(),
                    'per_page'     => $result->perPage(),
                    'total'        => $result->total(),
                ],
            ];
        } else {
            $result = $query->get();
            return [
                'items' => ($this->collection)::collection($result),
                'pagination' => null,
            ];
        }
    }


    public function getOne($id)
    {
        $object = $this->model::with($this->relations)->find($id);

        if (!$object) {
            throw new NotFoundException();
        }
        if ($this->resource) {
            return new ($this->resource)($object);
        }
        return $object;
    }

    protected function handleRelations($object, array &$data)
    {
        if (!property_exists($this, 'syncRelations')) {
            return;
        }

        foreach ($this->syncRelations as $relation => $requestKey) {

            if (!array_key_exists($requestKey, $data)) {
                continue;
            }

            $items = $data[$requestKey];
            unset($data[$requestKey]);

            if (!method_exists($object, $relation)) {
                continue;
            }

            $relationObj = $object->$relation();

            // many-to-many
            if (method_exists($relationObj, 'sync')) {

                $syncData = [];
                foreach ($items as $item) {
                    $id = $item['id'];
                    unset($item['id']);
                    $syncData[$id] = $item;
                    $relationObj->sync($syncData);
                }
            } else {
                // hasMany
                $relationObj->delete();
                foreach ($items as $item) {
                    $relationObj->create($item);
                }
            }
        }
    }

    protected function handleMedia($model, array $data)
    {
        if (!property_exists($this, 'mediaCollections')) return;

        $mediaService = new \App\Services\Base\MediaService();

        foreach ($this->mediaCollections as $field => $options) {
            if (!isset($data[$field])) continue;

            $files = $data[$field];
            $collection = $options['collection'] ?? $field;
            $type = $options['type'] ?? 'single';

            if ($type === 'single' && $files instanceof \Illuminate\Http\UploadedFile) {

                $mediaService->deleteByCollection($model, $collection);
                $mediaService->upload($model, $files, $collection);
            }

            if ($type === 'multiple' && is_array($files)) {
                $mediaService->uploadMultiple($model, $files, $collection);
            }
        }
    }


    public function create($data)
    {

        $object = $this->model::create($data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);
        return new $this->resource($object);
    }

    public function update($id, array $data)
    {

        $object = $this->model::findOrFail($id);

        $object->update($data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);

        return new $this->resource($object);
    }

    public function delete($id): bool
    {
        $object = $this->model::findOrFail($id);

        $object->delete();

        return true;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        foreach ($filters as $key => $value) {
            if ($value === null) continue;

            $query->where($key, $value);
        }

        if (!empty($config['search'])) {
            $search = $config['search'];
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%$search%");
                }
            });
        }

        if (!empty($config['sortField']) && in_array($config['sortField'], $this->sortableFields ?? [])) {
            $order = strtolower($config['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($config['sortField'], $order);
        }

        return $query;
    }

    /* ================= Images Logic ================= */

    protected function handleImages($model, array $data, bool $isUpdate = false): void
    {
        // --------- Relation images ---------
        if ($this->imagesRelation && $this->imagesFolder) {
            $this->handleRelationImages($model, $data, $isUpdate);
        }

        // --------- Column image ---------
        if ($this->imageColumn && $this->imageFolder) {
            $this->handleColumnImage($model, $data, $isUpdate);
        }
    }

    protected function handleRelationImages($model, array $data, bool $isUpdate): void
    {
        if ($isUpdate && !empty($data['deleted_images'])) {
            $model->{$this->imagesRelation}()
                ->whereIn('id', $data['deleted_images'])
                ->each(function ($image) {
                    $this->deleteFile('storage', $this->imagesFolder, $image->path);
                    $image->delete();
                });
        }

        if (!empty($data['images'])) {
            $mainIndex = $data['main_image_index'] ?? 0;

            foreach ($data['images'] as $index => $file) {
                $path = $this->uploadFile('storage', $this->imagesFolder, $file);

                if ($path) {
                    $model->{$this->imagesRelation}()->create([
                        'path'    => $path,
                        'is_main' => $index === $mainIndex,
                    ]);
                }
            }
        }
    }

    protected function handleColumnImage($model, array $data, bool $isUpdate): void
    {
        $column = $this->imageColumn;

        if (empty($data[$column])) {
            return;
        }

        if ($isUpdate && $model->{$column}) {
            $this->deleteFile('storage', $this->imageFolder, $model->{$column});
        }

        $path = $this->uploadFile('storage', $this->imageFolder, $data[$column]);

        if ($path) {
            $model->update([$column => $path]);
        }
    }

    protected function deleteImages($model): void
    {
        if ($this->imagesRelation && $this->imagesFolder) {
            $model->{$this->imagesRelation}?->each(function ($image) {
                $this->deleteFile('storage', $this->imagesFolder, $image->path);
                $image->delete();
            });
        }

        if ($this->imageColumn && $this->imageFolder && $model->{$this->imageColumn}) {
            $this->deleteFile('storage', $this->imageFolder, $model->{$this->imageColumn});
        }
    }
}
