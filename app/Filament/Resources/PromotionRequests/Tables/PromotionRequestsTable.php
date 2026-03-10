<?php

namespace App\Filament\Resources\PromotionRequests\Tables;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('custom.id')),

                Tables\Columns\TextColumn::make('shop.name')
                    ->label(__('custom.shop_name')),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('custom.type'))
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        PromotionType::OFFER => __('custom.offer'),
                        PromotionType::BANNER => __('custom.banner'),
                        default => $state->value ?? $state,
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('custom.title')),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('custom.status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        PromotionStatus::PENDING => __('custom.pending'),
                        PromotionStatus::APPROVED => __('custom.approved'),
                        PromotionStatus::REJECTED => __('custom.rejected'),
                        PromotionStatus::EXPIRED => __('custom.expired'),
                        default => $state->value ?? $state,
                    }),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label(__('custom.discount_percentage')),

                Tables\Columns\TextColumn::make('offer_starts_at')
                    ->label(__('custom.offer_starts_at')),

                Tables\Columns\TextColumn::make('offer_ends_at')
                    ->label(__('custom.offer_ends_at')),

                Tables\Columns\TextColumn::make('banner_position')
                    ->label(__('custom.banner_position')),

                Tables\Columns\TextColumn::make('banner_starts_at')
                    ->label(__('custom.banner_starts_at')),

                Tables\Columns\TextColumn::make('banner_ends_at')
                    ->label(__('custom.banner_ends_at')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.created_at')),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'offer' => 'عرض',
                        'banner' => 'بنر إعلاني',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'موافق عليه',
                        'rejected' => 'مرفوض',
                        'expired' => 'منتهي',
                    ]),

                Tables\Filters\SelectFilter::make('shop_id')
                    ->label('المتجر')
                    ->relationship('shop', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => $record->status === PromotionStatus::PENDING || $record->status === PromotionStatus::REJECTED),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
