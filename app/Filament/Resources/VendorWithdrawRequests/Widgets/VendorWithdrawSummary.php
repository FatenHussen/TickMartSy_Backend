<?php

namespace App\Filament\Resources\VendorWithdrawRequests\Widgets;

use App\Services\Admin\VendorAccountingService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class VendorWithdrawSummary extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return [];
        }

        $statement = app(VendorAccountingService::class)->getVendorStatement((int) $user->vendor_id, [], 5);
        $wallet = (array) data_get($statement, 'wallet', []);

        $available = (float) ($wallet['available_for_withdraw'] ?? 0);
        $pending = (float) ($wallet['pending_withdrawals'] ?? 0);
        $paid = (float) ($wallet['paid'] ?? 0);

        return [
            Stat::make(__('custom.withdrawals.available_for_withdraw'), $this->money($available))
                ->description(__('custom.withdrawals.available_for_withdraw_hint'))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make(__('custom.withdrawals.pending_withdrawals_amount'), $this->money($pending))
                ->description(__('custom.withdrawals.pending_requests_label', ['count' => (int) ($wallet['pending_requests_count'] ?? 0)]))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('custom.withdrawals.paid_withdrawals_amount'), $this->money($paid))
                ->description(__('custom.withdrawals.paid_requests_label', ['count' => (int) ($wallet['paid_requests_count'] ?? 0)]))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),
        ];
    }

    private function money(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}
