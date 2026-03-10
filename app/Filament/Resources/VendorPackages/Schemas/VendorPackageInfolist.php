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

                Section::make(__('custom.basic_package_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label(__('custom.package_name'))
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('price')
                            ->label(__('custom.price'))
                            ->money('USD')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('duration_days')
                            ->label(__('custom.subscription_duration'))
                            ->badge()
                            ->color('info'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label(__('custom.is_active'))
                            ->boolean(),
                    ])
                    ->columns([
                        'sm' => 2,
                        'xl' => 4,
                    ])
                    ->collapsible(),

                Section::make(__('custom.description'))
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('custom.description'))
                            ->columnSpanFull()
                            ->markdown(),
                    ])
                    ->collapsible(),

                Section::make(__('custom.product_features'))
                    ->schema([
                        Infolists\Components\TextEntry::make('max_products')
                            ->label(__('custom.max_products'))
                            ->badge()
                            ->color('primary')
                            ->default(__('custom.unlimited')),

                        Infolists\Components\IconEntry::make('is_featured')
                            ->label(__('custom.featured_products'))
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_premium_badge')
                            ->label(__('custom.premium_badge'))
                            ->boolean(),

                        Infolists\Components\TextEntry::make('search_priority')
                            ->label(__('custom.search_priority'))
                            ->badge()
                            ->default('-'),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Section::make(__('custom.marketing_features'))
                    ->schema([
                        Infolists\Components\TextEntry::make('max_campaigns')
                            ->label(__('custom.max_campaigns'))
                            ->badge()
                            ->color('warning')
                            ->default(__('custom.unlimited')),

                        Infolists\Components\IconEntry::make('has_banner_ad')
                            ->label(__('custom.banner_ads'))
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('custom.reports_analytics'))
                    ->schema([
                        Infolists\Components\IconEntry::make('has_sales_reports')
                            ->label(__('custom.sales_reports'))
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_analytics')
                            ->label(__('custom.analytics'))
                            ->boolean(),

                        Infolists\Components\TextEntry::make('report_level')
                            ->label(__('custom.report_level'))
                            ->badge()
                            ->color('info')
                            ->default('-'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make(__('custom.orders_delivery_features'))
                    ->schema([
                        Infolists\Components\TextEntry::make('order_priority')
                            ->label(__('custom.order_priority'))
                            ->badge()
                            ->default('-'),

                        Infolists\Components\IconEntry::make('can_set_prep_time')
                            ->label(__('custom.set_prep_time'))
                            ->boolean(),

                        Infolists\Components\IconEntry::make('custom_shipping_options')
                            ->label(__('custom.custom_shipping_options'))
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_vendor_delivery')
                            ->label(__('custom.vendor_delivery'))
                            ->boolean(),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Section::make(__('custom.commissions_fees'))
                    ->schema([
                        Infolists\Components\TextEntry::make('commission_rate')
                            ->label(__('custom.commission_rate'))
                            ->suffix('%')
                            ->badge()
                            ->color('danger')
                            ->default('-'),

                        Infolists\Components\TextEntry::make('commission_per_order')
                            ->label(__('custom.commission_per_order'))
                            ->money('USD')
                            ->color('danger')
                            ->default('-'),

                        Infolists\Components\IconEntry::make('activation_fee_waived')
                            ->label(__('custom.activation_fee_waived'))
                            ->boolean(),
                    ])
                    ->columns(3)
                    ->collapsible(),

            ]);
    }
}
