<?php

namespace App\Jobs;

use App\Models\Banner;
use App\Services\Base\SimpleFileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteBannerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $bannerId)
    {
    }

    public function handle(): void
    {
        $banner = Banner::find($this->bannerId);

        if (!$banner) {
            return;
        }

        if (!$banner->expires_at || $banner->expires_at->isFuture()) {
            return;
        }

        (new SimpleFileService())->delete($banner->image);
        $banner->delete();

        Log::info("DeleteBannerJob removed banner {$this->bannerId}");
    }
}
