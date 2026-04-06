<?php

namespace App\Services\Base;

use App\Jobs\SendFcmNotificationJob;
use App\Jobs\SendSmsJob;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(
        Model $recipient,
        string $title,
        string $body,
        array $data = [],
        array $channels = []
    ): void {
        $payload = $data;
        $channels = $this->filterChannelsForRecipient($recipient, $this->normalizeChannels($channels));

        Log::info("NotificationService", [
            'recipient_id' => $recipient->id,
            'recipient_type' => $recipient::class,
            'channels' => $channels,
        ]);

        try {
            if (in_array('fcm', $channels, true)) {
                $this->sendFcm($recipient, $title, $body, $payload);
            }

            if (in_array('sms', $channels, true)) {
                $this->sendSms($recipient, $title, $body, $payload);
            }

            $this->sendDatabase($recipient, $title, $body, $payload, $channels);
        } catch (\Throwable $e) {
            Log::error(
                'Notification failed',
                [
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    private function sendFcm(
        Model $recipient,
        string $title,
        string $body,
        array $data
    ): void {
        $tokens = $recipient->fcmTokens()
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (! empty($tokens)) {
            SendFcmNotificationJob::dispatch($tokens, $title, $body, $data);
            Log::info("send notification to user");
        } else {
            Log::info("no tokens to user");
        }
    }

    private function sendDatabase(
        Model $recipient,
        string $title,
        string $body,
        array $data,
        array $channels
    ): void {
        Log::info("sendDatabase Notification");
        $payload = array_merge($data, ['channels' => $channels]);
        $recipient->notify(
            new MessageNotification($title, $body, $payload, $channels)
        );
    }

    private function sendSms(
        Model $recipient,
        string $title,
        string $body,
        array $data
    ): void {
        $phone = $recipient->phone ?? null;

        if (empty($phone)) {
            return;
        }

        SendSmsJob::dispatch($phone, $title, $body, $data);
    }

    private function normalizeChannels(array $channels): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        fn ($channel) => strtolower($channel),
                        $channels
                    )
                )
            )
        );
    }

    private function filterChannelsForRecipient(Model $recipient, array $channels): array
    {
        $filtered = $channels;

        if (in_array('sms', $filtered, true) && empty($recipient->phone)) {
            $filtered = array_values(array_diff($filtered, ['sms']));
        }

        if (in_array('email', $filtered, true)) {
            $mailRecipient = method_exists($recipient, 'routeNotificationFor')
                ? $recipient->routeNotificationFor('mail')
                : ($recipient->email ?? null);

            if (empty($mailRecipient)) {
                $filtered = array_values(array_diff($filtered, ['email']));
            }
        }

        return $filtered;
    }



    public function get(Model $recipient)
    {
        return $recipient->notifications;
    }
}
