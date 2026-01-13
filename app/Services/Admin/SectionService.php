<?php

namespace App\Services\Admin;

use App\Http\Resources\Section\AllResource;
use App\Http\Resources\Section\OneResource;
use App\Models\Section;
use App\Services\BaseService;

class SectionService extends BaseService
{
    public function __construct(Section $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
        $this->pagination = true;
        $this->relations = ['sectionItems.item'];
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }
}
