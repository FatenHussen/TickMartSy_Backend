<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('الصور')
                    ->schema([
                        Infolists\Components\ImageEntry::make('media')
                            ->label('')
                            ->getStateUsing(fn($record) => $record->media->pluck('url')->toArray())
                            ->columnSpanFull(),
                    ]),

                Section::make('المعلومات الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('اسم المنتج'),

                        Infolists\Components\TextEntry::make('sku')
                            ->label('رمز المنتج'),

                        Infolists\Components\TextEntry::make('barcode')
                            ->label('الباركود'),

                        Infolists\Components\TextEntry::make('model')
                            ->label('الموديل'),

                        Infolists\Components\TextEntry::make('category.name')
                            ->label('الفئة'),

                        Infolists\Components\TextEntry::make('brand.name')
                            ->label('العلامة التجارية'),

                        Infolists\Components\TextEntry::make('vendor.name')
                            ->label('المورد'),

                        Infolists\Components\TextEntry::make('country')
                            ->label('بلد المنشأ'),
                    ])
                    ->columns(4),

                Section::make('الوصف')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف المختصر')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('full_description')
                            ->label('الوصف الكامل')
                            ->columnSpanFull()
                            ->html(),
                    ]),

                Section::make('الأسعار والكميات')
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->label('السعر')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('discount')
                            ->label('نسبة الخصم')
                            ->suffix('%')
                            ->default('-'),

                        Infolists\Components\TextEntry::make('price_after_discount')
                            ->label('السعر بعد الخصم')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('quantity')
                            ->label('الكمية المتاحة')
                            ->default('-'),
                    ])
                    ->columns(4),

                Section::make('إعدادات التوصيل')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_instant_delivery')
                            ->label('توصيل فوري')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('time_prepare')
                            ->label('وقت التحضير')
                            ->time('H:i')
                            ->default('-'),
                    ])
                    ->columns(2),

                Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('average_rating')
                            ->label('التقييم')
                            ->badge()
                            ->color('success')
                            ->default('0'),

                        Infolists\Components\TextEntry::make('sold_quantity')
                            ->label('الكمية المباعة')
                            ->badge()
                            ->default('0'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإضافة')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('المنتجات المشتراة معاً')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('boughtWithProduct')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('اسم المنتج'),

                                Infolists\Components\TextEntry::make('price')
                                    ->label('السعر')
                                    ->money('USD'),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn($record) => $record->bought_with && count($record->bought_with) > 0)
                    ->collapsible(),

                Section::make('متغيرات المنتج (Variants)')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('variants')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label('رقم المتغير'),

                                Infolists\Components\TextEntry::make('attributes_values')
                                    ->label('الخصائص')
                                    ->listWithLineBreaks()
                                    ->getStateUsing(function ($record) {
                                        $attributeValues = \App\Models\AttributeValue::whereIn('id', $record->attributes_values_ids ?? [])
                                            ->get();
                                        return $attributeValues->pluck('name')->toArray();
                                    })
                                    ->columnSpanFull(),

                                Infolists\Components\IconEntry::make('is_trend')
                                    ->label('رائج')
                                    ->boolean(),

                                Infolists\Components\TextEntry::make('average_rating')
                                    ->label('التقييم')
                                    ->badge()
                                    ->color('success'),

                                Infolists\Components\ImageEntry::make('media')
                                    ->label('الصور')
                                    ->getStateUsing(fn($record) => $record->media->pluck('url')->toArray())
                                    ->columnSpanFull(),

                                Infolists\Components\Section::make('توفر المتغير في المتاجر')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('shopVariants')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('shop.name')
                                                    ->label('المتجر'),

                                                Infolists\Components\TextEntry::make('quantity')
                                                    ->label('الكمية')
                                                    ->badge()
                                                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                                                Infolists\Components\TextEntry::make('price')
                                                    ->label('السعر')
                                                    ->money('USD'),

                                                Infolists\Components\TextEntry::make('created_at')
                                                    ->label('تاريخ الإضافة')
                                                    ->dateTime()
                                                    ->since(),
                                            ])
                                            ->columns(4),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible(),

                Section::make('تفاصيل الفئة')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('categoryDetails')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('categoryDetail.detail_key')
                                    ->label('المفتاح'),

                                Infolists\Components\TextEntry::make('detail_value')
                                    ->label('القيمة'),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn($record) => $record->categoryDetails->count() > 0)
                    ->collapsible(),

                Section::make('تفاصيل إضافية')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('extraDetails')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('detail_key')
                                    ->label('المفتاح'),

                                Infolists\Components\TextEntry::make('detail_value')
                                    ->label('القيمة'),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn($record) => $record->extraDetails->count() > 0)
                    ->collapsible(),
            ]);
    }
}
