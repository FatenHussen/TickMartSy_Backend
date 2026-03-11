<?php

namespace App\Filament\Pages;

use App\Models\VendorFcmToken;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Dashboard extends BaseDashboard
{
    public function __construct()
    {
        parent::__construct();

        // احفظ FCM token عند تحميل الـ Dashboard
        $this->saveFcmToken();
    }

    private function saveFcmToken()
    {
        try {
            $user = Auth::guard('vendor-user')->user();

            if (!$user) {
                Log::warning('Dashboard: User not authenticated');
                return;
            }

            Log::info('Dashboard: User authenticated', ['vendor_user_id' => $user->id]);

            // توليد token فريد
            $token = 'web_' . $user->id . '_' . time() . '_' . uniqid();

            // تحقق إذا كان التوكن موجود
            $existingToken = VendorFcmToken::where('vendor_user_id', $user->id)
                ->where('fcm_token', $token)
                ->first();

            if (!$existingToken) {
                VendorFcmToken::create([
                    'vendor_user_id' => $user->id,
                    'fcm_token' => $token,
                    'device_type' => 'web',
                ]);

                Log::info('Dashboard: FCM token saved', [
                    'vendor_user_id' => $user->id,
                    'token' => substr($token, 0, 20) . '...',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Dashboard: Error saving FCM token', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getWidgets(): array
    {
        return [
            // \App\Filament\Widgets\VendorNotificationsWidget::class,
            \App\Filament\Widgets\VendorStatsOverview::class,
            \App\Filament\Widgets\SalesChart::class,
            \App\Filament\Widgets\SalesHeatmap::class,
            \App\Filament\Widgets\TopProductsChart::class,
            \App\Filament\Widgets\OrdersStatusChart::class,
            \App\Filament\Widgets\LowStockProducts::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getViewData(): array
    {
        return [
            'fcmComponent' => \App\Livewire\SaveFcmToken::class,
        ];
    }
}
