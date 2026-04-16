<?php

namespace App\Filament\Resources\VendorWithdrawRequests;

use App\Filament\Resources\VendorWithdrawRequests\Pages\ListVendorWithdrawRequests;
use App\Filament\Resources\VendorWithdrawRequests\Tables\VendorWithdrawRequestsTable;
use App\Models\VendorWithdrawRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VendorWithdrawRequestResource extends Resource
{
    protected static ?string $model = VendorWithdrawRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?int $navigationSort = 92;

    public static function getNavigationLabel(): string
    {
        return __('custom.withdrawals.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('custom.withdrawals.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.withdrawals.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('vendor_id', $user->vendor_id)
            ->with('vendor');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return null;
        }

        $total = VendorWithdrawRequest::query()
            ->where('vendor_id', $user->vendor_id)
            ->count();

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return 'gray';
        }

        $hasPending = VendorWithdrawRequest::query()
            ->where('vendor_id', $user->vendor_id)
            ->where('status', 'pending')
            ->exists();

        return $hasPending ? 'warning' : 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return null;
        }

        $rows = VendorWithdrawRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->where('vendor_id', $user->vendor_id)
            ->groupBy('status')
            ->pluck('total', 'status');

        $pending = (int) ($rows['pending'] ?? 0);
        $paid = (int) ($rows['paid'] ?? 0);
        $rejected = (int) ($rows['rejected'] ?? 0);

        return "Pending: {$pending} | Paid: {$paid} | Rejected: {$rejected}";
    }

    public static function table(Table $table): Table
    {
        return VendorWithdrawRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorWithdrawRequests::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
