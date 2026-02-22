<?php

namespace App\Filament\Resources\VendorSubscriptions;

use App\Filament\Resources\VendorSubscriptions\Pages\ListVendorSubscriptions;
use App\Models\VendorSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VendorSubscriptionResource extends Resource
{
    protected static ?string $model = VendorSubscription::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;
    protected static ?string $navigationLabel = 'اشتراكي';
    protected static ?string $modelLabel = 'اشتراك';
    protected static ?string $pluralModelLabel = 'اشتراكاتي';
    protected static ?int $navigationSort = 91;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::guard('vendor-user')->user();
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Get shops that belong to this vendor user
        $shopIds = \App\Models\Shop::where('vendor_id', $user->vendor_id)->pluck('id');

        return parent::getEloquentQuery()
            ->whereIn('shop_id', $shopIds)
            ->with('package', 'shop');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shop.name')
                    ->label('المتجر')
                    ->formatStateUsing(fn($record) => $record->shop?->getTranslation('name', app()->getLocale()) ?? '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('package.name')
                    ->label('الباقة')
                    ->formatStateUsing(fn($record) => $record->package?->getTranslation('name', app()->getLocale()) ?? '-'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn($record) => $record->isExpiringSoon(7) ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'pending' => 'قيد الانتظار',
                        default => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'active' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('auto_renew')
                    ->label('تجديد تلقائي')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25]);
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
