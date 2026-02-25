<?php

namespace App\Filament\Resources\VendorPackages\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات الباقة')
                    ->schema([
                        Forms\Components\Placeholder::make('name')
                            ->label('اسم الباقة')
                            ->content(fn($record) => $record?->getTranslation('name', app()->getLocale()) ?? '-'),

                        Forms\Components\Placeholder::make('description')
                            ->label('الوصف')
                            ->content(fn($record) => $record?->getTranslation('description', app()->getLocale()) ?? '-')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('price')
                            ->label('السعر')
                            ->content(fn($record) => $record ? '$' . number_format($record->price, 2) : '-'),

                        Forms\Components\Placeholder::make('duration_days')
                            ->label('المدة')
                            ->content(fn($record) => $record ? $record->duration_days . ' يوم' : '-'),
                    ])
                    ->columns(2),

                Section::make('المميزات')
                    ->schema([
                        Forms\Components\Placeholder::make('max_products')
               ->label('الحد الأقصى للمنتجات')
                            ->content(fn($record) => $record?->max_products ?? 'غير محدود'),

                        Forms\Components\Placeholder::make('max_campaigns')
                            ->label('الحد الأقصى للحملات')
                            ->content(fn($record) => $record?->max_campaigns ?? 'غير محدود'),

                        Forms\Components\Placeholder::make('search_priority')
                            ->label('أولوية البحث')
                            ->content(fn($record) => $record?->search_priority ?? '-'),

                        Forms\Components\Placeholder::make('order_priority')
                            ->label('أولوية الطلبات')
                            ->content(fn($record) => $record?->order_priority ?? '-'),
                    ])
                    ->columns(2),

                Section::make('العمولات')
                    ->schema([
                        Forms\Components\Placeholder::make('commission_rate')
                            ->label('نسبة العمولة')
                            ->content(fn($record) => $record ? $record->commission_rate . '%' : '-'),

                        Forms\Components\Placeholder::make('commission_per_order')
                            ->label('عمولة ثابتة لكل طلب')
                            ->content(fn($record) => $record ? '$' . number_format($record->commission_per_order, 2) : '-'),
                    ])
                    ->columns(2),

                Section::make('المميزات الإضافية')
                    ->schema([
                        Forms\Components\Placeholder::make('is_featured')
                            ->label('باقة مميزة')
                            ->content(fn($record) => $record?->is_featured ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('has_premium_badge')
                            ->label('شارة مميزة')
                            ->content(fn($record) => $record?->has_premium_badge ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('has_banner_ad')
                            ->label('إعلانات بنر')
                            ->content(fn($record) => $record?->has_banner_ad ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('has_sales_reports')
                            ->label('تقارير المبيعات')
                            ->content(fn($record) => $record?->has_sales_reports ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('has_analytics')
                            ->label('التحليلات')
                            ->content(fn($record) => $record?->has_analytics ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('report_level')
                            ->label('مستوى التقارير')
                            ->content(fn($record) => $record?->report_level ?? '-'),

                        Forms\Components\Placeholder::make('can_set_prep_time')
                            ->label('تحديد وقت التحضير')
                            ->content(fn($record) => $record?->can_set_prep_time ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('custom_shipping_options')
                            ->label('خيارات شحن مخصصة')
                            ->content(fn($record) => $record?->custom_shipping_options ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('has_vendor_delivery')
                            ->label('توصيل البائع')
                            ->content(fn($record) => $record?->has_vendor_delivery ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('activation_fee_waived')
                            ->label('إعفاء من رسوم التفعيل')
                            ->content(fn($record) => $record?->activation_fee_waived ? '✓ نعم' : '✗ لا'),
                    ])
                    ->columns(2),

                Section::make('الحالة')
                    ->schema([
                        Forms\Components\Placeholder::make('is_active')
                            ->label('نشطة')
                            ->content(fn($record) => $record?->is_active ? '✓ نعم' : '✗ لا'),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->content(fn($record) => $record?->created_at?->format('Y-m-d H:i') ?? '-'),
                    ])
                    ->columns(2),
            ]);
    }
}

