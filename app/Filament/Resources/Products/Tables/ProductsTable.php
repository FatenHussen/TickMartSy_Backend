<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media')
                    ->label(__('custom.products.image'))
                    ->getStateUsing(fn($record) => $record->media->first()?->path)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('custom.products.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('custom.products.sku'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('custom.products.category'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('custom.products.brand'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('custom.products.price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_after_discount')
                    ->label(__('custom.products.price_after_discount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('custom.products.quantity'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('custom.products.rating'))
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label(__('custom.products.approval_status'))
                    ->badge()
                    ->color(fn(\App\Enums\ProductApprovalStatus $state): string => match ($state) {
                        \App\Enums\ProductApprovalStatus::PENDING => 'warning',
                        \App\Enums\ProductApprovalStatus::APPROVED => 'success',
                        \App\Enums\ProductApprovalStatus::REJECTED => 'danger',
                    })
                    ->formatStateUsing(fn(\App\Enums\ProductApprovalStatus $state): string => __('custom.products.approval_statuses.' . $state->value))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('custom.products.category'))
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label(__('custom.products.brand'))
                    ->relationship('brand', 'name'),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->label(__('custom.products.approval_status'))
                    ->options([
                        'pending' => __('custom.products.approval_statuses.pending'),
                        'approved' => __('custom.products.approval_statuses.approved'),
                        'rejected' => __('custom.products.approval_statuses.rejected'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            EditAction::make()
            ]);
    }
}
