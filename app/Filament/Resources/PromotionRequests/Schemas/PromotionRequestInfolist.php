<?php

namespace App\Filament\Resources\PromotionRequests\Schemas;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات أساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('shop.name')
                            ->label('المتجر')
                            ->formatStateUsing(fn($record) => $record->shop?->getTranslation('name', app()->getLocale()) ?? '-'),

                        Infolists\Components\TextEntry::make('type')
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

                        Infolists\Components\TextEntry::make('status')
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
                    ])
                    ->columns(3),

                Section::make('التفاصيل')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('العنوان')
                            ->formatStateUsing(fn($record) => $record->getTranslation('title', app()->getLocale())),

                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->formatStateUsing(fn($record) => $record->getTranslation('description', app()->getLocale()) ?: '-')
                            ->columnSpanFull(),

                        Infolists\Components\ImageEntry::make('images')
                            ->label('الصور')
                            ->columnSpanFull()
                            ->visible(fn($record) => !empty($record->images)),
                    ])
                    ->columns(2),

                // Offer details
                Section::make('تفاصيل العرض')
                    ->schema([
                        Infolists\Components\TextEntry::make('discount_percentage')
                            ->label('نسبة الخصم')
                            ->suffix('%'),

                        Infolists\Components\TextEntry::make('offer_starts_at')
                            ->label('تاريخ البداية')
                            ->date('Y-m-d'),

                        Infolists\Components\TextEntry::make('offer_ends_at')
                            ->label('تاريخ الانتهاء')
                            ->date('Y-m-d')
                            ->color(fn($record) => $record->isExpired() ? 'danger' : 'success'),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record->type === PromotionType::OFFER),

                // Banner details
                Section::make('تفاصيل البنر الإعلاني')
                    ->schema([
                        Infolists\Components\TextEntry::make('banner_position')
                            ->label('موقع البنر')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'home_top' => 'الصفحة الرئيسية - أعلى',
                                'home_middle' => 'الصفحة الرئيسية - وسط',
                                'home_bottom' => 'الصفحة الرئيسية - أسفل',
                                'category_top' => 'صفحة الفئات - أعلى',
                                'product_sidebar' => 'صفحة المنتج - جانبي',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('link_url')
                            ->label('رابط البنر')
                            ->url(fn($state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('banner_starts_at')
                            ->label('تاريخ البداية')
                            ->date('Y-m-d'),

                        Infolists\Components\TextEntry::make('banner_ends_at')
                            ->label('تاريخ الانتهاء')
                            ->date('Y-m-d')
                            ->color(fn($record) => $record->isExpired() ? 'danger' : 'success'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->type === PromotionType::BANNER),

                // Admin section
                Section::make('معلومات الموافقة')
                    ->schema([
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('approved_at')
                            ->label('تاريخ الموافقة')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('approvedBy.name')
                            ->label('تمت الموافقة بواسطة')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->status !== PromotionStatus::PENDING),

                // Timestamps
                Section::make('معلومات النظام')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}

