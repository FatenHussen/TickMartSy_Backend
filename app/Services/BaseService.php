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
    protected array $relations = [];
    protected bool $pagination = false;

    /* ================= Images ================= */

    protected ?string $imagesRelation = null; 
    protected ?string $imagesFolder   = null; 
    protected ?string $imageColumn = null;    
    protected ?string $imageFolder = null;    

    /* ================= CRUD ================= */

    public function getAll(array $filters = [])
    {
        $query = $this->model::query()->with($this->relations);
        $query = $this->queryBuilder($query, $filters);

        if ($this->pagination) {
            $perPage = request('per_page', 10);
            $result  = $query->paginate($perPage);
        } else {
            $result = $query->get();
        }

        return ($this->collection)::collection($result);
    }

    public function getOne($id)
    {
        $object = $this->model::with($this->relations)->find($id);

        if (!$object) {
            throw new NotFoundException();
        }

        return new $this->resource($object);
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $object = $this->model::create($data);

            $this->handleImages($object, $data);

            DB::commit();

            return new $this->resource($object->load($this->relations));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();

        $object = $this->model::find($id);

        if (!$object) {
            throw new NotFoundException();
        }

        try {
            $object->update($data);

            $this->handleImages($object, $data, true);

            DB::commit();

            return new $this->resource($object->load($this->relations));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id): bool
    {
        $object = $this->model::find($id);

        if (!$object) {
            throw new NotFoundException();
        }

        $this->deleteImages($object);

        $object->delete();

        return true;
    }

    /* ================= Filters ================= */

    protected function queryBuilder(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($key, ['name', 'title', 'description'])) {
                $query->where($key, 'LIKE', "%{$value}%");
            } else {
                $query->where($key, $value);
            }
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
