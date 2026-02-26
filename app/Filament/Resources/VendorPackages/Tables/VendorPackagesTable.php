<?php

namespace App\Filament\Resources\VendorPackages\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class VendorPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الباقة')
                    ->formatStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('المدة (أيام)')
                    ->suffix(' يوم')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_products')
                    ->label('عدد المنتجات')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميزة')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_premium_badge')
                    ->label('شارة مميزة')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_analytics')
                    ->label('تحليلات')
                    ->boolean(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('نسبة العمولة')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشطة')
                    ->placeholder('الكل')
                    ->trueLabel('نشطة فقط')
                    ->falseLabel('غير نشطة فقط'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميزة')
     ->placeholder('الكل')
                    ->trueLabel('مميزة فقط')
                    ->falseLabel('عادية فقط'),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('price', 'asc');
    }
}

