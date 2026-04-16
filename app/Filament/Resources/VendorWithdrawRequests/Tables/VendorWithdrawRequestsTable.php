<?php

namespace App\Filament\Resources\VendorWithdrawRequests\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;

class VendorWithdrawRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('custom.id'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('custom.withdrawals.amount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('custom.withdrawals.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('custom.status_pending'),
                        'paid' => __('custom.withdrawals.status_paid'),
                        'rejected' => __('custom.withdrawals.status_rejected'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('custom.withdrawals.payment_method'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'bank_transfer' => __('custom.withdrawals.method_bank_transfer'),
                        'cash' => __('custom.withdrawals.method_cash'),
                        'wallet' => __('custom.withdrawals.method_wallet'),
                        'other' => __('custom.withdrawals.method_other'),
                        default => '-',
                    }),

                Tables\Columns\TextColumn::make('transfer_reference')
                    ->label(__('custom.withdrawals.transfer_reference'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label(__('custom.withdrawals.requested_at'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label(__('custom.withdrawals.processed_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('custom.withdrawals.status'))
                    ->options([
                        'pending' => __('custom.status_pending'),
                        'paid' => __('custom.withdrawals.status_paid'),
                        'rejected' => __('custom.withdrawals.status_rejected'),
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('custom.withdrawals.payment_method'))
                    ->options([
                        'bank_transfer' => __('custom.withdrawals.method_bank_transfer'),
                        'cash' => __('custom.withdrawals.method_cash'),
                        'wallet' => __('custom.withdrawals.method_wallet'),
                        'other' => __('custom.withdrawals.method_other'),
                    ]),

                Tables\Filters\Filter::make('requested_at')
                    ->label(__('custom.withdrawals.requested_at'))
                    ->form([
                        DatePicker::make('from')->label(__('custom.withdrawals.from_date')),
                        DatePicker::make('to')->label(__('custom.withdrawals.to_date')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('requested_at', '>=', $date))
                            ->when($data['to'] ?? null, fn ($q, $date) => $q->whereDate('requested_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
