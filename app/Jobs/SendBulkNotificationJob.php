<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Driver;
use App\Models\User;
use App\Models\VendorUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Base\NotificationService;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $title;
    public string $body;
    public array $types;
    public array $data;
    public array $channels;
    public array $recipientIdsByType;

    public function __construct(
        string $title,
        string $body,
        string|array $types,
        array $channels = ['fcm'],
        array $data = [],
        array $recipientIdsByType = []
    ) {
        $this->title = $title;
        $this->body  = $body;
        $this->types = $this->normalizeTypes($types);
        $this->channels = $this->normalizeChannels($channels);
        $this->data  = $data;
        $this->recipientIdsByType = $this->normalizeRecipientIds($recipientIdsByType);
    }

    public function handle(NotificationService $notificationService)
    {
        $payload = $this->data;

        foreach ($this->resolveRecipientClasses() as $type => $recipientClass) {
            $query = $recipientClass::query();
            $ids = $this->recipientIdsByType[$type] ?? [];
            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            }

            $query->chunkById(200, function (Collection $recipients) use ($notificationService, $payload) {
                $recipients->each(function (Model $recipient) use ($notificationService, $payload) {
                    $notificationService->send(
                        $recipient,
                        $this->title,
                        $this->body,
                        $payload,
                        $this->channels
                    );
                });
            });
        }
    }

    private function resolveRecipientClasses(): array
    {
        $mapping = [
            'user' => User::class,
            'driver' => Driver::class,
            'vendor' => VendorUser::class,
            'admin' => Admin::class,
        ];

        if (in_array('all', $this->types, true)) {
            return $mapping;
        }

        return array_intersect_key($mapping, array_flip($this->types));
    }

    private function normalizeChannels(array $channels): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map('strtolower', $channels)
                )
            )
        );
    }

    private function normalizeTypes(string|array $types): array
    {
        if (! is_array($types)) {
            $types = array_filter(explode(',', $types));
        }

        $allowedTypes = ['all', 'user', 'driver', 'vendor', 'admin'];
        $normalizedTypes = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn($type) => strtolower(trim((string) $type)),
                        $types
                    ),
                    static fn($type) => in_array($type, $allowedTypes, true)
                )
            )
        );

        return empty($normalizedTypes) ? ['all'] : $normalizedTypes;
    }

    private function normalizeRecipientIds(array $recipientIdsByType): array
    {
        $normalized = [];
        foreach ($recipientIdsByType as $type => $ids) {
            if (! is_array($ids)) {
                continue;
            }

            $normalized[(string) $type] = array_values(
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

        return $normalized;
    }
}
