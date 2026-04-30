<?php

namespace App\Services\Admin;

use App\Http\Resources\ContactMethod\AllResource;
use App\Http\Resources\ContactMethod\OneResource;
use App\Models\ContactMethod;
use App\Services\BaseService;

class ContactMethodService extends BaseService
{
    public function __construct(ContactMethod $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->singleImages = ['icon'];
        $this->pagination = true;
        $this->searchableFields = ['id', 'type', 'value'];
        $this->sortableFields = ['id', 'type', 'created_at'];
    }
}
