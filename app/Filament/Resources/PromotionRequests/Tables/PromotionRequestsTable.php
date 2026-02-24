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
                    ->label('الرقم')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shop.name')
                    ->label('المتجر')
                    ->formatStateUsing(fn($record) => $record->shop?->getTranslation('name', app()->getLocale()) ?? '-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        PromotionType::OFFER => 'عرض',
                        PromotionType::BANNER => 'بنر إعلاني',
                        default => $state->value ?? $state,
                    })
                    ->color(fn($state) => match ($state) {
                        PromotionType::OFFER => 'success',
                        PromotionType::BANNER => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->formatStateUsing(fn($record) => $record->getTranslation('title', app()->getLocale()))
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        PromotionStatus::PENDING => 'قيد المراجعة',
                        PromotionStatus::APPROVED => 'موافق عليه',
                        PromotionStatus::REJECTED => 'مرفوض',
                        PromotionStatus::EXPIRED => 'منتهي',
                        default => $state->value ?? $state,
                    })
                    ->color(fn($state) => match ($state) {
                        PromotionStatus::PENDING => 'warning',
                        PromotionStatus::APPROVED => 'success',
                        PromotionStatus::REJECTED => 'danger',
                        PromotionStatus::EXPIRED => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('نسبة الخصم')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($record) => $record?->type === PromotionType::OFFER),

                Tables\Columns\TextColumn::make('offer_starts_at')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($record) => $record?->type === PromotionType::OFFER),

                Tables\Columns\TextColumn::make('offer_ends_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($record) => $record?->type === PromotionType::OFFER),

                Tables\Columns\TextColumn::make('banner_position')
                    ->label('موقع البنر')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'home_top' => 'الصفحة الرئيسية - أعلى',
                        'home_middle' => 'الصفحة الرئيسية - وسط',
                        'home_bottom' => 'الصفحة الرئيسية - أسفل',
                        'category_top' => 'صفحة الفئات - أعلى',
                        'product_sidebar' => 'صفحة المنتج - جانبي',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($record) => $record?->type === PromotionType::BANNER),

                Tables\Columns\TextColumn::make('banner_starts_at')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($record) => $record?->type === PromotionType::BANNER),

                Tables\Columns\TextColumn::make('banner_ends_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($record) => $record?->type === PromotionType::BANNER),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

