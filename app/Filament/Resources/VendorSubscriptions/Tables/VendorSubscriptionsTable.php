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
                Tables\Columns\TextColumn::make('package.name')
                    ->label(__('custom.subscription_package')),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('custom.subscription_starts_at')),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('custom.subscription_ends_at')),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('custom.subscription_status'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        'active' => __('custom.status_active'),
                        'expired' => __('custom.status_expired'),
                        'cancelled' => __('custom.status_cancelled'),
                        'pending' => __('custom.status_pending'),
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('auto_renew')
                    ->label(__('custom.subscription_auto_renew')),

            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
