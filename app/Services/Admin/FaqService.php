<?php

namespace App\Services\Admin;

use App\Http\Resources\Faq\AllResource;
use App\Http\Resources\Faq\AdminOneResource;

use App\Jobs\SendBulkNotificationJob;
use App\Models\Faq;
use App\Models\LegalDocument;
use App\Services\BaseService;

class FaqService extends BaseService
{
    public function __construct(Faq $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'question', 'answer', 'type'];
    }
}
