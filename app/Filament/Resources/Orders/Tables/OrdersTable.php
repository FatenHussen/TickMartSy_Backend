<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('custom.orders.order_number'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('custom.orders.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('custom.orders.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        OrderStatus::PENDING->value => 'warning',
                        OrderStatus::PREPARING->value => 'info',
                        OrderStatus::OUT_DELIVERY->value => 'primary',
                        OrderStatus::DELIVERED->value => 'success',
                        OrderStatus::CANCELLED->value => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        OrderStatus::PENDING->value => __('custom.orders.statuses.pending'),
                        OrderStatus::PREPARING->value => __('custom.orders.statuses.preparing'),
                        OrderStatus::OUT_DELIVERY->value => __('custom.orders.statuses.out_delivery'),
                        OrderStatus::DELIVERED->value => __('custom.orders.statuses.delivered'),
                        OrderStatus::CANCELLED->value => __('custom.orders.statuses.cancelled'),
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('custom.orders.total'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label(__('custom.orders.total_quantity'))
                    ->sortable(),


                Tables\Columns\IconColumn::make('is_instant_delivery')
                    ->label(__('custom.orders.is_instant_delivery'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.orders.order_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('custom.orders.status'))
                    ->options([
                        OrderStatus::PENDING->value => __('custom.orders.statuses.pending'),
                        OrderStatus::PREPARING->value => __('custom.orders.statuses.preparing'),
                        OrderStatus::OUT_DELIVERY->value => __('custom.orders.statuses.out_delivery'),
                        OrderStatus::DELIVERED->value => __('custom.orders.statuses.delivered'),
                        OrderStatus::CANCELLED->value => __('custom.orders.statuses.cancelled'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_instant_delivery')
                    ->label(__('custom.orders.is_instant_delivery'))
                    ->placeholder(__('custom.all'))
                    ->trueLabel(__('custom.orders.delivery_types.instant'))
                    ->falseLabel(__('custom.orders.delivery_types.normal')),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('start_preparing')
                    ->label(__('custom.orders.actions.start_preparing'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === OrderStatus::PENDING->value)
                    ->action(function ($record) {
                        $record->update([
                            'status' => OrderStatus::PREPARING->value,
                            'preparing_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('custom.orders.actions.status_updated'))
                            ->success()
                            ->send();
                    }),

                Action::make('ready_for_delivery')
                    ->label(__('custom.orders.actions.ready_for_delivery'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === OrderStatus::PREPARING->value)
                    ->action(function ($record) {
                        $record->update([
                            'status' => OrderStatus::OUT_DELIVERY->value,
                            'out_delivery_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('custom.orders.actions.ready_notification'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
