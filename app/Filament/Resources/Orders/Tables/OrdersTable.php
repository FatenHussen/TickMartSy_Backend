<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
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
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_code')
                    ->label('كود التوصيل')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        OrderStatus::PENDING->value => 'warning',
                        OrderStatus::PREPARING->value => 'info',
                        OrderStatus::OUT_DELIVERY->value => 'primary',
                        OrderStatus::DELIVERED->value => 'success',
                        // OrderStatus::CANCELLED->value => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        OrderStatus::PENDING->value => 'قيد الانتظار',
                        OrderStatus::PREPARING->value => 'قيد التحضير',
                        OrderStatus::OUT_DELIVERY->value => 'في التوصيل',
                        OrderStatus::DELIVERED->value => 'تم التوصيل',
                        // OrderStatus::CANCELLED->value => 'ملغي',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('عدد المنتجات')
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_instant_delivery')
                    ->label('توصيل فوري')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        OrderStatus::PENDING->value => 'قيد الانتظار',
                        OrderStatus::PREPARING->value => 'قيد التحضير',
                        OrderStatus::OUT_DELIVERY->value => 'في التوصيل',
                        OrderStatus::DELIVERED->value => 'تم التوصيل',
                        // OrderStatus::CANCELLED->value => 'ملغي',
                    ]),

                Tables\Filters\TernaryFilter::make('is_instant_delivery')
                    ->label('توصيل فوري')
                    ->placeholder('الكل')
                    ->trueLabel('فوري')
                    ->falseLabel('عادي'),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
