<?php

namespace App\Filament\Resources\VendorSubscriptions\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class VendorSubscriptionsTable
{
    public static function configure(Table $table): Table
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
                    ->formatStateUsing(fn($record) => $record->package?->getTranslation('name', app()->getLocale()) ?? '-')
                    ->searchable(),

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
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('auto_renew')
                    ->label('تجديد تلقائي')
                    ->boolean(),
            ])
            ->filters([
              Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'pending' => 'قيد الانتظار',
                    ]),

                Tables\Filters\SelectFilter::make('shop_id')
                    ->label('المتجر')
                    ->relationship('shop', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}

