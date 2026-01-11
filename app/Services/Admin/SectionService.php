<?php

namespace App\Services\Admin;

use App\Http\Resources\Section\AllResource;
use App\Http\Resources\Section\OneResource;
use App\Models\Admin;
use App\Models\Section;
use App\Models\Service;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

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
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }
}
