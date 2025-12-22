<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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
        
        $response = Http::withHeaders([
            'authorization' => 'c31ee59586cc4ca9ce31b3f50628eda75fcc44b58728f35802ab1ccf0c1d587f',
            'Content-Type'  => 'application/json',
        ])->post('https://otp.octopus-software.online/send', [
            'to'      => $this->phone,
            'message' => "This is OTP : {$this->otp}",
        ]);
    }
}
