<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShopInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make(__('custom.shops.sections.basic_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label(__('custom.shops.name')),

                        Infolists\Components\TextEntry::make('description')
                            ->label(__('custom.shops.description'))
                            ->columnSpanFull(),

                        Infolists\Components\ImageEntry::make('logo')->disk('public')
                            ->label(__('custom.shops.logo')),
                    ])
                    ->columns(2),

                Section::make(__('custom.shops.sections.contact_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('phone')
                            ->label(__('custom.shops.phone')),

                        Infolists\Components\TextEntry::make('mobile')
                            ->label(__('custom.shops.mobile')),

                        Infolists\Components\TextEntry::make('email')
                            ->label(__('custom.shops.email')),
                    ])
                    ->columns(3),

                Section::make(__('custom.shops.sections.location_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('address')
                            ->label(__('custom.shops.address'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('area.name')
                            ->label(__('custom.shops.area')),

                        Infolists\Components\TextEntry::make('lat')
                            ->label(__('custom.shops.lat')),

                        Infolists\Components\TextEntry::make('lng')
                            ->label(__('custom.shops.lng')),
                    ])
                    ->columns(3),

                Section::make(__('custom.shops.sections.ratings_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('average_rating')
                            ->label(__('custom.shops.rating'))
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('ratings_count')
                            ->label(__('custom.shops.ratings_count'))
                            ->badge(),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label(__('custom.shops.is_active'))
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_free_delivery')
                            ->label(__('custom.shops.is_free_delivery'))
                            ->boolean(),
                    ])
                    ->columns(4),
            ]);
    }
}
