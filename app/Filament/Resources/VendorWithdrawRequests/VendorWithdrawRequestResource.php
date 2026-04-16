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
