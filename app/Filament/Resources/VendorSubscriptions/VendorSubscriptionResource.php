<?php

namespace App\Filament\Resources\VendorSubscriptions;

use App\Filament\Resources\VendorSubscriptions\Pages\ListVendorSubscriptions;
use App\Models\VendorSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class VendorSubscriptionResource extends Resource
{
    protected static ?string $model = VendorSubscription::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('custom.my_subscription_navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('custom.my_subscription_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.my_subscription_plural_model_label');
    }
    protected static ?int $navigationSort = 91;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::guard('vendor-user')->user();
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        if (!Schema::hasColumn('vendor_subscriptions', 'vendor_id')) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('vendor_id', $user->vendor_id)
            ->with('package', 'vendor');
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\VendorSubscriptions\Tables\VendorSubscriptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorSubscriptions::route('/'),
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
