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

    public function update($id, array $data)
    {
        $object = parent::update($id, $data);

        $notificationService = app(\App\Services\Base\NotificationService::class);

        $statusText = match ($object->status->value) {
            'rejected' => 'تم رفض الشكوى',
            'resolved' => 'تم حل الشكوى',
            default => 'تم تحديث حالة الشكوى',
        };

        $body = $statusText;

        if ($object->admin_response) {
            $body .= " - السبب: {$object->admin_response}";
        }

        $notificationService->send(
            recipient: $object->user,
            title: "تحديث على الشكوى",
            body: $body,
            data: [
                'type' => 'complaint',
            ]
        );

        return $object;
    }
}
