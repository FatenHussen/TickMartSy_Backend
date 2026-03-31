<?php

namespace App\Services\Base;

use App\Jobs\SendFcmNotificationJob;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(
        Model $recipient,
        string $title,
        string $body,
        array $data = []
    ): void {

        Log::info("NotificationService");
        try {
            $this->sendFcm($recipient, $title, $body, $data);
            $this->sendDatabase($recipient, $title, $body, $data);
            //send f
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
        array $data

    ): void {
        Log::info("sendDatabase Notification");
        $recipient->notify(
            new MessageNotification($title, $body, $data)
        );
    }



    public function get(Model $recipient)
    {
        return $recipient->notifications;
    }
}
