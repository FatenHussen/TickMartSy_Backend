<?php

namespace App\Services\Admin;

use App\Http\Resources\PageSection\AllResource;
use App\Http\Resources\PageSection\OneResource;
use App\Models\PageSection;
use App\Services\BaseService;

class PageSectionService extends BaseService
{
    public function __construct(PageSection $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
        $this->pagination = true;
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }
}
