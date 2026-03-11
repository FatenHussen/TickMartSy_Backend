<?php

namespace App\Services\Admin;

use App\Http\Resources\AdminNotification\AllResource;
use App\Jobs\SendBulkNotificationJob;
use App\Models\AdminNotification;
use App\Services\BaseService;

class AdminNotificationService extends BaseService
{
    public function __construct(AdminNotification $model)
    {
        $this->model      = $model;
        $this->resource   = AllResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'title', 'body', 'type'];
    }

    public function create($data)
    {
        $object = AdminNotification::create($data);

        SendBulkNotificationJob::dispatch(
            $object->title,
            $object->body,
            $object->type,
            $data['is_fixed']
        );

        return $object;
    }
}
