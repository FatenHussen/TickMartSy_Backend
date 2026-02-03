<?php

namespace App\Jobs;

use App\Helpers\SendFCMNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendFcmNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $tokens = [], public string $title, public string $body, public array $data)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // (new SendFCMNotification($this->tokens, $this->title, $this->body, $this->data))->sendNotification();
    }
}
