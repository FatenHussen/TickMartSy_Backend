<?php

namespace App\Jobs;

use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExpirePointsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PointService $pointService): void
    {
        $expiredCount = $pointService->expireOldPoints();
        
        Log::info("Points expiration job completed. Expired {$expiredCount} wallets.");
    }
}
