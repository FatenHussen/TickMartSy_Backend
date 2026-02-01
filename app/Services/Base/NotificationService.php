<?php

namespace App\Services\Base;

use App\Helpers\SendFCMNotification;
use App\Jobs\SendFcmNotificationJob;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use tidy;

class NotificationService
{
    /**
     * Send an appointment-related notification
     */
    public static function
    sendAppointmentNotification(Model $recipient, string $title, string $body, array $data = []): void
    {
        try {
            $tokens = $recipient->fcmTokens()->pluck('fcm_token')->toArray();

            Log::info('Tokens');
            Log::info($tokens);

            if (count($tokens)) {
                SendFcmNotificationJob::dispatch($tokens, $title, $body, $data);
            }

            $recipient->notify(new MessageNotification($title, $body));
        } catch (\Exception $e) {
            Log::error("Failed to send notification to {$recipient->id}: " . $e->getMessage());
        }
    }

    public  function get(Model $recipient)
    {
        return $recipient->notifications;
    }
}
