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
        $targetTypes = $this->resolveTargetTypes($data);
        $recipientIdsByType = $this->resolveRecipientIdsByType($data);

        $object = AdminNotification::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => implode(',', $targetTypes),
            'target_page' => $data['target_page'] ?? null,
            'emoji' => $data['emoji'] ?? null,
            'media_type' => $mediaPayload['type'] ?? null,
            'media_url' => $mediaPayload['url'] ?? null,
            'channels' => $channels,
        ]);

        SendBulkNotificationJob::dispatch(
            $object->title,
            $object->body,
            $targetTypes,
            $channels,
            $this->prepareJobData($data, $mediaPayload, $targetTypes, $recipientIdsByType),
            $recipientIdsByType
        );

        return $object;
    }

    private function resolveTargetTypes(array $data): array
    {
        $types = $data['types'] ?? [$data['type'] ?? 'all'];
        if (! is_array($types)) {
            $types = [$types];
        }

        $allowedTypes = ['all', 'driver', 'user', 'vendor'];
        $normalizedTypes = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn($type) => strtolower((string) $type),
                        $types
                    ),
                    static fn($type) => in_array($type, $allowedTypes, true)
                )
            )
        );

        if (empty($normalizedTypes)) {
            return ['all'];
        }

        if (in_array('all', $normalizedTypes, true)) {
            return ['all'];
        }

        return $normalizedTypes;
    }

    private function resolveRecipientIdsByType(array $data): array
    {
        return [
            'driver' => $this->normalizeIds($data['driver_ids'] ?? []),
            'user' => $this->normalizeIds($data['user_ids'] ?? []),
            'vendor' => $this->normalizeIds($data['vendor_ids'] ?? []),
        ];
    }

    private function normalizeIds(array $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn($id) => is_numeric($id) ? (int) $id : null,
                        $ids
                    ),
                    static fn($id) => ! is_null($id)
                )
            )
        );
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

    private function prepareJobData(array $data, ?array $media, array $targetTypes, array $recipientIdsByType): array
    {
        return [
            'type' => 'admin',
            'target_page' => $data['target_page'] ?? null,
            'emoji' => $data['emoji'] ?? null,
            'media_type' => $media['type'] ?? null,
            'media_url' => $media['url'] ?? null,
            'target_types' => $targetTypes,
            'recipient_ids' => $recipientIdsByType,
        ];
    }
}
