<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class VendorNotificationNotification extends Notification
{
    public function __construct(
        private string $title,
        private string $body,
        private string $type,
        private array $data = []
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage(
            data: array_merge($this->data, [
                'title' => $this->title,
                'body' => $this->body,
                'type' => $this->type,
            ])
        );
    }
}
