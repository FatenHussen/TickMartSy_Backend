<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist->columns(1)
            ->schema([

                Section::make(__('custom.products.sections.basic_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label(__('custom.products.name'))
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('sku')
                            ->label(__('custom.products.sku'))
                            ->badge(),

                        Infolists\Components\TextEntry::make('barcode')
                            ->label(__('custom.products.barcode'))
                            ->badge(),

                        Infolists\Components\TextEntry::make('model')
                            ->label(__('custom.products.model'))
                            ->badge(),

                        Infolists\Components\TextEntry::make('category.name')
                            ->label(__('custom.products.category'))
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('brand.name')
                            ->label(__('custom.products.brand'))
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('vendor.name')
                            ->label(__('custom.products.vendor'))
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('country')
                            ->label(__('custom.products.country'))
                            ->badge(),

                        Infolists\Components\TextEntry::make('approval_status')
                            ->label(__('custom.products.approval_status'))
                            ->badge()
                            ->color(fn(\App\Enums\ProductApprovalStatus $state): string => match ($state) {
                                \App\Enums\ProductApprovalStatus::PENDING => 'warning',
                                \App\Enums\ProductApprovalStatus::APPROVED => 'success',
                                \App\Enums\ProductApprovalStatus::REJECTED => 'danger',
                            })
                            ->formatStateUsing(fn(\App\Enums\ProductApprovalStatus $state): string => __('custom.products.approval_statuses.' . $state->value)),

                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label(__('custom.products.rejection_reason'))
                            ->columnSpanFull()
                            ->visible(fn($record) => $record->approval_status === \App\Enums\ProductApprovalStatus::REJECTED),
                    ])
                    ->columns([
                        'sm' => 2,
                        'xl' => 4,
                    ])
                    ->collapsible(),

                Section::make(__('custom.products.sections.description'))
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('custom.products.description'))
                            ->columnSpanFull()
                            ->markdown(),

                        Infolists\Components\TextEntry::make('full_description')
                            ->label('الوصف الكامل')
                            ->columnSpanFull()
                            ->html(),
                    ])
                    ->collapsible(),

                Section::make(__('custom.products.sections.pricing'))
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->label(__('custom.products.price'))
                            ->money('USD')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('discount')
                            ->label(__('custom.products.discount'))
                            ->suffix('%')
                            ->badge()
                            ->color('danger')
                            ->default('-'),

                        Infolists\Components\TextEntry::make('price_after_discount')
                            ->label(__('custom.products.price_after_discount'))
                            ->money('USD')
                            ->color('primary')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('quantity')
                            ->label(__('custom.products.quantity_available'))
                            ->badge()
                            ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                            ->default('-'),
                    ])
                    ->columns(4)
                    ->collapsible(),
Section::make(__('custom.products.sections.images'))
                    ->schema([
                        Infolists\Components\ImageEntry::make('media')
                            ->label('')->disk('public')
                            ->getStateUsing(fn($record) => $record->media->pluck('path')->toArray())
                            ->columnSpanFull()
                            ->height(120)
                            ->extraAttributes(['class' => 'rounded-xl']),
                    ])
                    ->collapsible(false),
                Section::make(__('custom.products.sections.delivery_settings'))
                    ->schema([
                        Infolists\Components\IconEntry::make('is_instant_delivery')
                            ->label(__('custom.products.is_instant_delivery'))
                            ->boolean(),

                        Infolists\Components\TextEntry::make('time_prepare')
                            ->label(__('custom.products.time_prepare'))
                            ->formatStateUsing(fn($state) => $state ? $state->format('H:i') : '-')
                            ->badge(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('custom.products.statistics'))
                    ->schema([
                        Infolists\Components\TextEntry::make('average_rating')
                            ->label(__('custom.products.rating'))
                            ->badge()
                            ->color('success')
                            ->size('lg')
                            ->default('0'),

                        Infolists\Components\TextEntry::make('sold_quantity')
                            ->label(__('custom.products.sold_quantity'))
                            ->badge()
                            ->color('primary')
                            ->default('0'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('custom.products.date_added'))
                            ->dateTime()
                            ->since(),
                    ])
                    ->columns(3)
                    ->collapsible(),


                Section::make(__('custom.products.sections.variants'))
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('variants')
                            ->label('')
                            ->schema([


                                Infolists\Components\ViewEntry::make('attributes_display')
                                    ->label(__('custom.products.sections.attributes'))
                                    ->view('filament.infolists.variant-attributes')
                                    ->columnSpanFull(),

                                Infolists\Components\IconEntry::make('is_trend')
                                    ->label(__('custom.products.trending'))
                                    ->boolean(),

                                Infolists\Components\TextEntry::make('average_rating')
                                    ->label(__('custom.products.rating'))
                                    ->badge()
                                    ->color('success'),

                                Infolists\Components\ImageEntry::make('media')
                                    ->label(__('custom.products.sections.images'))
                                    ->getStateUsing(fn($record) => $record->media->pluck('path')->toArray())
                                    ->columnSpanFull()->disk('public')
                                    ->height(90),

                                Section::make(__('custom.products.sections.shop_availability'))
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('shopVariants')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('shop.name')
                                                    ->label(__('custom.products.shop'))
                                                    ->weight('bold'),

                                                Infolists\Components\TextEntry::make('quantity')
                                                    ->label(__('custom.products.quantity'))
                                                    ->badge()
                                                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                                                Infolists\Components\TextEntry::make('price')
                                                    ->label(__('custom.products.price'))
                                                    ->money('USD')
                                                    ->color('success'),

                                                Infolists\Components\TextEntry::make('created_at')
                                                    ->label(__('custom.products.date_added'))
                                                    ->since(),
                                            ])
                                            ->columns(4),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'xl' => 3,
                            ]),
                    ])->columnSpanFull()
                    ->collapsible(),


                Section::make(__('custom.products.category_details'))
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('categoryDetails')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('categoryDetail.detail_key')
                                    ->label(__('custom.products.detail_key'))
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('detail_value')
                                    ->label(__('custom.products.detail_value')),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn($record) => $record->categoryDetails->count() > 0)
                    ->collapsible(),

                Section::make(__('custom.products.extra_details'))
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('extraDetails')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('detail_key')
                                    ->label(__('custom.products.detail_key'))
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('detail_value')
                                    ->label(__('custom.products.detail_value')),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn($record) => $record->extraDetails->count() > 0)
                    ->collapsible(),Section::make(__('custom.products.sections.bought_with'))
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('bought_with_products_list')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('custom.products.name'))
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('price')
                                    ->label(__('custom.products.price'))
                                    ->money('USD')
                                    ->color('success'),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn($record) => $record->bought_with && count($record->bought_with) > 0)
                    ->collapsible(),
            ]);
    }
}
