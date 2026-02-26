<?php

namespace App\Filament\Resources\PromotionRequests\Schemas;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PromotionRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Tabs::make('promotion_info_tabs')
                ->tabs([
                    // Tab 1: المعلومات الأساسية
                    Tab::make('المعلومات الأساسية')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('معلومات عامة')
                                ->schema([
                                    Infolists\Components\TextEntry::make('shop.name')
                                        ->label('المتجر')
                                        ->formatStateUsing(fn($record) => $record->shop?->getTranslation('name', app()->getLocale()) ?? '-')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-building-storefront')
                                        ->size('lg')
                                        ->weight('bold'),

                                    Infolists\Components\TextEntry::make('type')
                                        ->label('نوع الترويج')
                                        ->badge()
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            PromotionType::OFFER => '🏷️ عرض',
                                            PromotionType::BANNER => '📢 بنر إعلاني',
                                            default => $state->value ?? $state,
                                        })
                                        ->color(fn($state) => match ($state) {
                                            PromotionType::OFFER => 'success',
                                            PromotionType::BANNER => 'info',
                                            default => 'gray',
                                        })
                                        ->size('lg'),

                                    Infolists\Components\TextEntry::make('status')
                                        ->label('الحالة')
                                        ->badge()
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            PromotionStatus::PENDING => '⏳ قيد المراجعة',
                                            PromotionStatus::APPROVED => '✅ موافق عليه',
                                            PromotionStatus::REJECTED => '❌ مرفوض',
                                            PromotionStatus::EXPIRED => '⌛ منتهي',
                                            default => $state->value ?? $state,
                                        })
                                        ->color(fn($state) => match ($state) {
                                            PromotionStatus::PENDING => 'warning',
                                            PromotionStatus::APPROVED => 'success',
                                            PromotionStatus::REJECTED => 'danger',
                                            PromotionStatus::EXPIRED => 'gray',
                                            default => 'gray',
                                        })
                                        ->size('lg'),
                                ])
                                ->columns(3)
                                ->collapsible(),

                            Section::make('التفاصيل')
                                ->schema([
                                    Infolists\Components\TextEntry::make('title')
                                        ->label('العنوان')
                                        ->formatStateUsing(fn($record) => $record->getTranslation('title', app()->getLocale()))
                                        ->weight('bold')
                                        ->size('lg')
                                        ->color('primary')
                                        ->columnSpanFull(),

                                    Infolists\Components\TextEntry::make('description')
                                        ->label('الوصف')
                                        ->formatStateUsing(fn($record) => $record->getTranslation('description', app()->getLocale()) ?: '-')
                                        ->columnSpanFull()
                                        ->prose(),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 2: الصور
                    Tab::make('الصور')
                        ->icon('heroicon-o-photo')
                        ->badge(fn($record) => !empty($record->images) ? count($record->images) : null)
                        ->schema([
                            Section::make('صور الترويج')
                                ->schema([
                                    Infolists\Components\ImageEntry::make('images')
                                        ->label('')
                                        ->disk('public')
                                        ->columnSpanFull()
                                        ->height(200)
                                        ->extraAttributes(['class' => 'rounded-xl'])
                                        ->visible(fn($record) => !empty($record->images)),

                                    Infolists\Components\TextEntry::make('no_images')
                                        ->label('')
                                        ->default('لا توجد صور')
                                        ->color('gray')
                                        ->icon('heroicon-o-photo')
                                        ->columnSpanFull()
                                        ->visible(fn($record) => empty($record->images)),
                                ])
                                ->collapsible(false),
                        ]),

                    // Tab 3: تفاصيل العرض
                    Tab::make('تفاصيل العرض')
                        ->icon('heroicon-o-tag')
                        ->visible(fn($record) => $record->type === PromotionType::OFFER)
                        ->schema([
                            Section::make('معلومات العرض')
                                ->schema([
                                    Infolists\Components\TextEntry::make('discount_percentage')
                                        ->label('نسبة الخصم')
                                        ->suffix('%')
                                        ->badge()
                                        ->color('danger')
                                        ->size('xl')
                                        ->weight('bold')
                                        ->icon('heroicon-o-receipt-percent'),

                                    Infolists\Components\TextEntry::make('offer_starts_at')
                                        ->label('تاريخ بداية العرض')
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-calendar'),

                                    Infolists\Components\TextEntry::make('offer_ends_at')
                                        ->label('تاريخ انتهاء العرض')
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->icon(fn($record) => $record->isExpired() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),

                                    Infolists\Components\TextEntry::make('duration')
                                        ->label('مدة العرض')
                                        ->formatStateUsing(function ($record) {
                                            if (!$record->offer_starts_at || !$record->offer_ends_at) {
                                                return '-';
                                            }
                                            $days = $record->offer_starts_at->diffInDays($record->offer_ends_at);
                                            return $days . ' يوم';
                                        })
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-clock'),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make('حالة العرض')
                                ->schema([
                                    Infolists\Components\TextEntry::make('is_expired')
                                        ->label('حالة العرض')
                                        ->formatStateUsing(fn($record) => $record->isExpired() ? '⌛ منتهي' : '✅ نشط')
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->size('lg'),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 4: تفاصيل البنر
                    Tab::make('تفاصيل البنر')
                        ->icon('heroicon-o-rectangle-stack')
                        ->visible(fn($record) => $record->type === PromotionType::BANNER)
                        ->schema([
                            Section::make('معلومات البنر الإعلاني')
                                ->schema([
                                    Infolists\Components\TextEntry::make('banner_position')
                                        ->label('موقع البنر')
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'home_top' => '🏠 الصفحة الرئيسية - أعلى',
                                            'home_middle' => '🏠 الصفحة الرئيسية - وسط',
                                            'home_bottom' => '🏠 الصفحة الرئيسية - أسفل',
                                            'category_top' => '📂 صفحة الفئات - أعلى',
                                            'product_sidebar' => '📦 صفحة المنتج - جانبي',
                                            default => $state,
                                        })
                                        ->badge()
                                        ->color('info')
                                        ->size('lg')
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('link_url')
                                        ->label('رابط البنر')
                                        ->url(fn($state) => $state)
                                        ->openUrlInNewTab()
                                        ->placeholder('لا يوجد رابط')
                                        ->icon('heroicon-o-link')
                                        ->color('primary')
                                        ->copyable()
                                        ->copyMessage('تم نسخ الرابط')
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('banner_starts_at')
                                        ->label('تاريخ بداية البنر')
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-calendar'),

                                    Infolists\Components\TextEntry::make('banner_ends_at')
                                        ->label('تاريخ انتهاء البنر')
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->icon(fn($record) => $record->isExpired() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),

                                    Infolists\Components\TextEntry::make('duration')
                                        ->label('مدة البنر')
                                        ->formatStateUsing(function ($record) {
                                            if (!$record->banner_starts_at || !$record->banner_ends_at) {
                                                return '-';
                                            }
                                            $days = $record->banner_starts_at->diffInDays($record->banner_ends_at);
                                            return $days . ' يوم';
                                        })
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-clock'),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make('حالة البنر')
                                ->schema([
                                    Infolists\Components\TextEntry::make('is_expired')
                                        ->label('حالة البنر')
                                        ->formatStateUsing(fn($record) => $record->isExpired() ? '⌛ منتهي' : '✅ نشط')
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->size('lg'),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 5: معلومات الموافقة
                    Tab::make('معلومات الموافقة')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->badge(fn($record) => $record->status !== PromotionStatus::PENDING ? '✓' : null)
                        ->schema([
                            Section::make('تفاصيل الموافقة')
                                ->schema([
                                    Infolists\Components\TextEntry::make('admin_notes')
                                        ->label('ملاحظات الإدارة')
                                        ->placeholder('لا توجد ملاحظات')
                                        ->columnSpanFull()
                                        ->prose()
                                        ->color(fn($record) => $record->status === PromotionStatus::REJECTED ? 'danger' : 'gray'),

                                    Infolists\Components\TextEntry::make('approved_at')
                                        ->label('تاريخ الموافقة')
                                        ->dateTime('Y-m-d H:i')
                                        ->placeholder('-')
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-calendar')
                                        ->visible(fn($record) => $record->approved_at !== null),

                                    Infolists\Components\TextEntry::make('approvedBy.name')
                                        ->label('تمت الموافقة بواسطة')
                                        ->placeholder('-')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-user')
                                        ->visible(fn($record) => $record->approved_by !== null),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->visible(fn($record) => $record->status !== PromotionStatus::PENDING),

                            Section::make('حالة الطلب')
                                ->schema([
                                    Infolists\Components\TextEntry::make('pending_message')
                                        ->label('')
                                        ->default('⏳ الطلب قيد المراجعة من قبل الإدارة')
                                        ->color('warning')
                                        ->size('lg')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn($record) => $record->status === PromotionStatus::PENDING),
                        ]),

                    // Tab 6: معلومات النظام
                    Tab::make('معلومات النظام')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('التواريخ')
                                ->schema([
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('تاريخ الإنشاء')
                                        ->dateTime('Y-m-d H:i')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-calendar')
                                        ->since(),

                                    Infolists\Components\TextEntry::make('updated_at')
                                        ->label('آخر تحديث')
                                        ->dateTime('Y-m-d H:i')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-clock')
                                        ->since(),

                                    Infolists\Components\TextEntry::make('id')
                                        ->label('رقم الطلب')
                                        ->badge()
                                        ->color('info')
                                        ->copyable()
                                        ->copyMessage('تم نسخ رقم الطلب'),

                                    Infolists\Components\TextEntry::make('vendor.name')
                                        ->label('البائع')
                                        ->badge()
                                        ->color('warning')
                                        ->icon('heroicon-o-building-office'),
                                ])
                                ->columns(4)
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
