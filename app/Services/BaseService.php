<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Log;

class BaseService
{
    protected $model;
    protected $resource;
    protected $collection;
    protected $relations = [];
    protected $pagination;
    protected $syncRelations=[];
    protected $mediaCollections=[];


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
        $object = $this->model::find($id);
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
                $relationObj->sync($syncData);} 

            }
            else {
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
        Log::info($object);
        $this->handleRelations($object,$data);
        $this->handleMedia($object,$data);
        return new $this->resource($object);
    }

    public function update($id, $data)
    {
        $object = $this->model::find($id);
        if (!$object) {
            throw new NotFoundException();
        }
        $object->update($data);
        $this->handleRelations($object,$data);
        $this->handleMedia($object,$data);

        return new $this->resource($object);
    }

    public function delete($id)
    {
        $object = $this->model::find($id);
        if (!$object) {
            throw new NotFoundException();
        }
        $object->delete();
        return true;
    }

    // public function queryBuilder($query, $filters)
    // {
    //     foreach ($filters as $key => $value) {
    //         if (in_array($key, ['name', 'description'])) {
    //             $query->where($key, 'LIKE', "%$value%");
    //         } else {
    //             $query->where($key, $value);
    //         }
    //     }

    //     return $query;
    // }

    
    public function queryBuilder($query, $filters = [], $config = [])
    {
        foreach ($filters as $key => $value) {
            if ($value === null) continue;

            $query->where($key, $value);
        }

        if (!empty($config['search']) && !empty($config['searchable'])) {
            $search = $config['search'];
            $query->where(function ($q) use ($search, $config) {
                foreach ($config['searchable'] as $field) {
                    $q->orWhere($field, 'LIKE', "%$search%");
                }
            });
        }

        if (!empty($config['sortField']) && in_array($config['sortField'], $config['sortable'] ?? [])) {
            $order = strtolower($config['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($config['sortField'], $order);
        }

        return $query;
    }
}
