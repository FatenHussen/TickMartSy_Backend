<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FcmTokenWidget;
use App\Models\VendorWithdrawRequest;
use App\Services\Admin\VendorAccountingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createWithdrawRequest')
                ->label(__('custom.withdrawals.new_request'))
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    TextInput::make('amount')
                        ->label(__('custom.withdrawals.amount'))
                        ->numeric()
                        ->minValue(0.01)
                        ->required(),
                    Select::make('payment_method')
                        ->label(__('custom.withdrawals.payment_method'))
                        ->options([
                            'bank_transfer' => __('custom.withdrawals.method_bank_transfer'),
                            'cash' => __('custom.withdrawals.method_cash'),
                            'wallet' => __('custom.withdrawals.method_wallet'),
                            'other' => __('custom.withdrawals.method_other'),
                        ])
                        ->required(),
                    TextInput::make('note')
                        ->label(__('custom.withdrawals.note'))
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $user = Auth::guard('vendor-user')->user();

                    if (!$user) {
                        Notification::make()
                            ->title(__('custom.withdrawals.not_authorized'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $vendorId = (int) $user->vendor_id;

                    $hasPendingRequest = VendorWithdrawRequest::query()
                        ->where('vendor_id', $vendorId)
                        ->where('status', 'pending')
                        ->exists();

                    if ($hasPendingRequest) {
                        Notification::make()
                            ->title(__('custom.withdrawals.pending_request_exists'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $statement = app(VendorAccountingService::class)->getVendorStatement($vendorId);
                    $available = (float) data_get($statement, 'wallet.available_for_withdraw', 0);
                    $amount = (float) ($data['amount'] ?? 0);

                    if ($amount <= 0 || $amount > $available) {
                        Notification::make()
                            ->title(__('custom.withdrawals.amount_exceeds_balance'))
                            ->body(__('custom.withdrawals.available_balance_label', ['amount' => '$' . number_format($available, 2)]))
                            ->danger()
                            ->send();

                        return;
                    }

                    VendorWithdrawRequest::query()->create([
                        'vendor_id' => $vendorId,
                        'amount' => round($amount, 2),
                        'status' => 'pending',
                        'payment_method' => $data['payment_method'],
                        'note' => $data['note'] ?? null,
                        'requested_at' => now(),
                    ]);

                    Notification::make()
                        ->title(__('custom.withdrawals.request_created'))
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\VendorStatsOverview::class,
            \App\Filament\Widgets\SalesChart::class,
            \App\Filament\Widgets\SalesHeatmap::class,
            \App\Filament\Widgets\TopProductsChart::class,
            \App\Filament\Widgets\OrdersStatusChart::class,
            \App\Filament\Widgets\LowStockProducts::class,
        ];
    }
}
