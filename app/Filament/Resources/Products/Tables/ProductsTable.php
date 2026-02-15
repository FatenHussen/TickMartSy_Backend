<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media')
                    ->label('الصورة')
                    ->getStateUsing(fn($record) => $record->media->first()?->url)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('رمز المنتج')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_after_discount')
                    ->label('السعر بعد الخصم')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label('التقييم')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
