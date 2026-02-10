<?php

namespace App\Services\Admin;

use App\Http\Resources\Complaint\AllResource;
use App\Http\Resources\Complaint\OneResource;
use App\Models\Complaint;
use App\Services\BaseService;

class ComplaintService extends BaseService
{
    public function __construct(Complaint $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['user', 'order'];
        $this->searchableFields = ['id', 'name', 'type'];
    }
}
