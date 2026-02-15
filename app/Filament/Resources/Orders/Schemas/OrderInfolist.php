<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('معلومات الطلب')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('رقم الطلب'),

                        Infolists\Components\TextEntry::make('delivery_code')
                            ->label('كود التوصيل')
                            ->copyable()
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                OrderStatus::PENDING->value => 'warning',
                                OrderStatus::PREPARING->value => 'info',
                                OrderStatus::OUT_DELIVERY->value => 'primary',
                                OrderStatus::DELIVERED->value => 'success',
                                // OrderStatus::CANCELLED->value => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                OrderStatus::PENDING->value => 'قيد الانتظار',
                                OrderStatus::PREPARING->value => 'قيد التحضير',
                                OrderStatus::OUT_DELIVERY->value => 'في التوصيل',
                                OrderStatus::DELIVERED->value => 'تم التوصيل',
                                // OrderStatus::CANCELLED->value => 'ملغي',
                                default => $state,
                            }),

                        Infolists\Components\IconEntry::make('is_instant_delivery')
                            ->label('توصيل فوري')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الطلب')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('معلومات العميل')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('اسم العميل'),

                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('هاتف العميل'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('بريد العميل'),
                    ])
                    ->columns(3),

                Section::make('عنوان التوصيل')
                    ->schema([
                        Infolists\Components\TextEntry::make('address.address')
                            ->label('العنوان')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('address.area.name')
                            ->label('المنطقة'),

                        Infolists\Components\TextEntry::make('address.city.name')
                            ->label('المدينة'),
                    ])
                    ->columns(2),

                Section::make('معلومات السائق')
                    ->schema([
                        Infolists\Components\TextEntry::make('driver.name')
                            ->label('اسم السائق'),

                        Infolists\Components\TextEntry::make('driver.phone')
                            ->label('هاتف السائق'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->driver_id !== null),

                Section::make('المبالغ المالية')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('المجموع الفرعي')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('basket_discount')
                            ->label('خصم السلة')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('coupon_discount')
                            ->label('خصم الكوبون')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('delivery_price')
                            ->label('سعر التوصيل')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('total')
                            ->label('المجموع الكلي')
                            ->money('USD')
                            ->size('lg')
                            ->weight('bold'),
                    ])
                    ->columns(5),

                Section::make('منتجات الطلب')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('shopProductVariant.productVariant.product.name')
                                    ->label('المنتج'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('الكمية'),

                                Infolists\Components\TextEntry::make('price')
                                    ->label('السعر')
                                    ->money('USD'),

                                Infolists\Components\TextEntry::make('total')
                                    ->label('المجموع')
                                    ->money('USD')
                                    ->getStateUsing(fn($record) => $record->price * $record->quantity),
                            ])
                            ->columns(4),
                    ]),

                Section::make('التواريخ')
                    ->schema([
                        Infolists\Components\TextEntry::make('pending_at')
                            ->label('وقت الانتظار')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('preparing_at')
                            ->label('وقت التحضير')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('out_delivery_at')
                            ->label('وقت الخروج للتوصيل')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('delivered_at')
                            ->label('وقت التوصيل')
                            ->dateTime(),
                    ])
                    ->columns(4)
                    ->collapsible(),
            ]);
    }
}
