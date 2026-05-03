<?php

namespace App\Services\Admin;

use App\Http\Resources\LegalDocument\AllResource;
use App\Http\Resources\LegalDocument\OneResource;

use App\Models\LegalDocument;
use App\Services\BaseService;

class LegalDocumentService extends BaseService
{
    public function __construct(LegalDocument $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'key', 'title', 'content'];
        $this->sortableFields   = ['id', 'key', 'created_at', 'updated_at'];
    }
}
