<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageNotification extends Notification
{
    use Queueable;

    private $title;
    private $body;
    private $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $body, array $data)
    {
        $this->title = $title;
        $this->body  = $body;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'data' => $this->data
        ];
    }
}
