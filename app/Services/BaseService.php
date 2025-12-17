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
    public function getAll($filters = [])
    {
        $query = $this->model::query()->with($this->relations);
        $query = $this->queryBuilder($query, $filters);

        if ($this->pagination) {
            $perPage = request('per_page', 10);
            $result = $query->paginate($perPage);
        } else {
            $result = $query->get();
        }

        return ($this->collection)::collection($result);
    }
    public function getOne($id)
    {
        $object = $this->model::find($id);
        if (!$object) {
            throw new NotFoundException();
        }
        return new ($this->resource)($object);
    }

    public function create($data)
    {
        $object = $this->model::create($data);
        return new $this->resource($object);
    }

    public function update($id, $data)
    {
        $object = $this->model::find($id);
        if (!$object) {
            throw new NotFoundException();
        }
        $object->update($data);
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

    public function queryBuilder($query, $filters)
    {
        foreach ($filters as $key => $value) {
            if (in_array($key, ['name', 'description'])) {
                $query->where($key, 'LIKE', "%$value%");
            } else {
                $query->where($key, $value);
            }
        }

        return $query;
    }
}
