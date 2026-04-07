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
    public string $type;
    public array $data;
    public array $channels;

    public function __construct(
        string $title,
        string $body,
        string $type,
        array $channels = ['fcm'],
        array $data = []
    ) {
        $this->title = $title;
        $this->body  = $body;
        $this->type  = $type;
        $this->channels = $this->normalizeChannels($channels);
        $this->data  = $data;
    }

    public function handle(NotificationService $notificationService)
    {
        $payload = $this->data;

        foreach ($this->resolveRecipientClasses() as $recipientClass) {
            $recipientClass::query()->chunkById(200, function (Collection $recipients) use ($notificationService, $payload) {
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
        return match ($this->type) {
            'user' => [User::class],
            'driver' => [Driver::class],
            'admin' => [Admin::class],
            'all' => [User::class, Driver::class, Admin::class],
            default => [],
        };
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
}
