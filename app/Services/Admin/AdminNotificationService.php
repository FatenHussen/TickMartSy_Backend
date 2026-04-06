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
        $mediaPayload = $this->resolveMediaPayload($data);

        $channels = $data['channels'] ?? ['fcm'];

        $object = AdminNotification::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'target_page' => $data['target_page'] ?? null,
            'emoji' => $data['emoji'] ?? null,
            'media_type' => $mediaPayload['type'] ?? null,
            'media_url' => $mediaPayload['url'] ?? null,
            'channels' => $channels,
        ]);

        SendBulkNotificationJob::dispatch(
            $object->title,
            $object->body,
            $object->type,
            $channels,
            $this->prepareJobData($data, $mediaPayload)
        );

        return $object;
    }

    private function resolveMediaPayload(array $data): ?array
    {
        if (empty($data['media'])) {
            return null;
        }

        $storedUrl = $this->storeMediaAndGetUrl($data['media']);
        if (! $storedUrl) {
            return null;
        }

        $payload = [
            'type' => $this->detectUploadedMediaType($data['media']),
            'url' => $storedUrl,
        ];

        return $payload;
    }

    private function storeMediaAndGetUrl($file): ?string
    {
        $path = $this->uploadFile('public', 'notifications', $file);
        return $path ? asset('storage/' . $path) : null;
    }

    private function detectUploadedMediaType($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return $extension === 'gif' ? 'gif' : 'image';
    }

    private function prepareJobData(array $data, ?array $media): array
    {
        return [
            'type' => 'admin',
            'target_page' => $data['target_page'] ?? null,
            'emoji' => $data['emoji'] ?? null,
            'media_type' => $media['type'] ?? null,
            'media_url' => $media['url'] ?? null,
        ];
    }
}
