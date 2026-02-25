<?php

namespace App\Filament\Resources\VendorPackages\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorPackageInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist->columns(1)
            ->schema([

                Section::make('معلومات الباقة الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('اسم الباقة')
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('price')
                            ->label('السعر')
                            ->money('USD')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('duration_days')
                            ->label('مدة الاشتراك (أيام)')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('نشط')
                            ->boolean(),
                    ])
                    ->columns([
                        'sm' => 2,
                        'xl' => 4,
                    ])
                    ->collapsible(),

                Section::make('الوصف')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull()
                            ->markdown(),
                    ])
                    ->collapsible(),

                Section::make('ميزات المنتجات')
                    ->schema([
                        Infolists\Components\TextEntry::make('max_products')
                            ->label('الحد الأقصى للمنتجات')
                            ->badge()
                            ->color('primary')
                            ->default('غير محدود'),

                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('منتجات مميزة')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_premium_badge')
                            ->label('شارة مميزة')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('search_priority')
                            ->label('أولوية البحث')
                            ->badge()
                            ->default('-'),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Section::make('ميزات التسويق')
                    ->schema([
                        Infolists\Components\TextEntry::make('max_campaigns')
                            ->label('الحد الأقصى للحملات')
                            ->badge()
                            ->color('warning')
                            ->default('غير محدود'),

                        Infolists\Components\IconEntry::make('has_banner_ad')
                            ->label('إعلانات بانر')
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('التقارير والتحليلات')
                    ->schema([
                        Infolists\Components\IconEntry::make('has_sales_reports')
                            ->label('تقارير المبيعات')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_analytics')
                            ->label('التحليلات')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('report_level')
                            ->label('مستوى التقارير')
                            ->badge()
                            ->color('info')
                            ->default('-'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('ميزات الطلبات والتوصيل')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_priority')
                            ->label('أولوية الطلبات')
                            ->badge()
                            ->default('-'),

                        Infolists\Components\IconEntry::make('can_set_prep_time')
                            ->label('تحديد وقت التحضير')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('custom_shipping_options')
                            ->label('خيارات شحن مخصصة')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_vendor_delivery')
                            ->label('توصيل البائع')
                            ->boolean(),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Section::make('العمولات والرسوم')
                    ->schema([
                        Infolists\Components\TextEntry::make('commission_rate')
                            ->label('نسبة العمولة')
                            ->suffix('%')
                            ->badge()
                            ->color('danger')
                            ->default('-'),

                        Infolists\Components\TextEntry::make('commission_per_order')
                            ->label('عمولة لكل طلب')
                            ->money('USD')
                            ->color('danger')
                            ->default('-'),

                        Infolists\Components\IconEntry::make('activation_fee_waived')
                            ->label('إعفاء من رسوم التفعيل')
                            ->boolean(),
                    ])
                    ->columns(3)
                    ->collapsible(),

            ]);
    }
}

