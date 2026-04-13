<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Product;
use App\Services\Base\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyExpiredProductsJob implements ShouldQueue
{
    use Queueable;

    public function handle(NotificationService $notificationService): void
    {
        $today = now()->toDateString();

        Product::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $today)
            ->whereNull('expiry_notified_at')
            ->chunkById(100, function ($products) use ($notificationService): void {
                $admins = Admin::query()->where('is_active', true)->get();

                if ($admins->isEmpty()) {
                    return;
                }

                foreach ($products as $product) {
                    foreach ($admins as $admin) {
                        $notificationService->send(
                            $admin,
                            'انتهاء صلاحية منتج',
                            "انتهت صلاحية المنتج {$product->id} ({$product->name}).",
                            [
                                'type' => 'product_expired',
                                'product_id' => (string) $product->id,
                                'expiry_date' => optional($product->expiry_date)->format('Y-m-d'),
                            ]
                        );
                    }

                    $product->forceFill([
                        'expiry_notified_at' => now(),
                    ])->save();
                }
            });
    }
}
