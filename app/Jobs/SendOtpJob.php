<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $timeout = 10;

    protected $phone;
    protected $otp;

    public function __construct(string $phone, string $otp)
    {
        $this->phone = $phone;
        $this->otp   = $otp;
    }

    public function handle(): void
    {
        Log::info('SMS OTP skipped (no provider). Use static code on the OTP screen.', [
            'phone' => $this->phone,
        ]);
    }
}
