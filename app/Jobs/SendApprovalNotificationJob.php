<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendApprovalNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 10;

    public function __construct(
        protected string $phone,
        protected string $view,
        protected array $data = []
    ) {
    }

    public function handle(): void
    {
        $html = view($this->view, $this->data)->render();
        $message = $this->htmlToWhatsAppText($html);

        $response = Http::withHeaders([
            'authorization' => 'a12f1ba8f6e805b2c4d0e8cb1dcc7d19a0e24fa3f97e9f679',
            'Content-Type' => 'application/json',
        ])->post('https://otp.octopus-software.online/send', [
            'to' => $this->phone,
            'message' => $message,
        ]);

        Log::info('Approval notification sent', [
            'phone' => $this->phone,
            'view' => $this->view,
            'response' => $response->json(),
        ]);
    }

    protected function htmlToWhatsAppText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text ?? '');

        return trim($text);
    }
}
