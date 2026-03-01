<?php

namespace App\Services\Admin;

use App\Http\Resources\Badge\AdminOneResource;
use App\Http\Resources\Badge\OneResource;

use App\Jobs\SendBulkNotificationJob;
use App\Models\Badge;
use App\Models\Faq;
use App\Models\LegalDocument;
use App\Services\BaseService;

class BadgeService extends BaseService
{
    public function __construct(Badge $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = OneResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'question', 'answer', 'type'];
    }
}
