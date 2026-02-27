<?php

namespace App\Services\Admin;

use App\Http\Resources\PageSection\AdminOneResource;
use App\Http\Resources\PageSection\AllResource;
use App\Http\Resources\PageSection\OneResource;
use App\Models\DisplayType;
use App\Models\PageSection;
use App\Services\BaseService;

class PageSectionService extends BaseService
{
    public function __construct(PageSection $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
        $this->pagination = true;
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }

    public function displayTypes($manual_model)
    {
        return DisplayType::where('manual_model', $manual_model)->select('id', 'image')->get();
    }
}
