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
                Section::make('معلومات المتجر')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('اسم المتجر'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        Infolists\Components\ImageEntry::make('logo')
                            ->label('الشعار'),
                    ])
                    ->columns(2),

                Section::make('معلومات التواصل')
                    ->schema([
                        Infolists\Components\TextEntry::make('phone')
                            ->label('الهاتف'),

                        Infolists\Components\TextEntry::make('mobile')
                            ->label('الموبايل'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('البريد الإلكتروني'),
                    ])
                    ->columns(3),

                Section::make('العنوان والموقع')
                    ->schema([
                        Infolists\Components\TextEntry::make('address')
                            ->label('العنوان')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('area.name')
                            ->label('المنطقة'),

                        Infolists\Components\TextEntry::make('lat')
                            ->label('خط العرض'),

                        Infolists\Components\TextEntry::make('lng')
                            ->label('خط الطول'),
                    ])
                    ->columns(3),

                Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('average_rating')
                            ->label('التقييم')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('ratings_count')
                            ->label('عدد التقييمات')
                            ->badge(),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('نشط')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_free_delivery')
                            ->label('توصيل مجاني')
                            ->boolean(),
                    ])
                    ->columns(4),
            ]);
    }
}
