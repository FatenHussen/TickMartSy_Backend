<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ShopForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Shop Information')
                    ->tabs([
                        // Tab 1: Basic Information (English)
                        Tabs\Tab::make(__('custom.shops.tabs.basic_info_en'))
                            ->schema([
                                Forms\Components\TextInput::make('name.en')
                                    ->label(__('custom.shops.name') . ' (English)')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description.en')
                                    ->label(__('custom.shops.description') . ' (English)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('address.en')
                                    ->label(__('custom.shops.address') . ' (English)')
                                    ->rows(2),
                            ]),

                        // Tab 2: Basic Information (Arabic)
                        Tabs\Tab::make(__('custom.shops.tabs.basic_info_ar'))
                            ->schema([
                                Forms\Components\TextInput::make('name.ar')
                                    ->label(__('custom.shops.name') . ' (العربية)')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description.ar')
                                    ->label(__('custom.shops.description') . ' (العربية)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('address.ar')
                                    ->label(__('custom.shops.address') . ' (العربية)')
                                    ->rows(2),
                            ]),

                        // Tab 3: Contact & Location
                        Tabs\Tab::make(__('custom.shops.tabs.contact_location'))
                            ->schema([
                                Section::make(__('custom.shops.sections.contact_info'))
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->label(__('custom.shops.phone'))
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('mobile')
                                            ->label(__('custom.shops.mobile'))
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('email')
                                            ->label(__('custom.shops.email'))
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(3),

                                Section::make(__('custom.shops.sections.location_info'))
                                    ->schema([
                                        Forms\Components\TextInput::make('lat')
                                            ->label(__('custom.shops.lat'))
                                            ->numeric(),

                                        Forms\Components\TextInput::make('lng')
                                            ->label(__('custom.shops.lng'))
                                            ->numeric(),

                                        Forms\Components\Select::make('area_id')
                                            ->label(__('custom.shops.area'))
                                            ->relationship('area', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(3),
                            ]),

                        // Tab 4: Media
                        Tabs\Tab::make(__('custom.shops.tabs.media'))
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label(__('custom.shops.logo'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('shop')
                                    ->imageEditor()
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('cover_images')
                                    ->label(__('custom.shops.cover_images'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('shop/covers')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),

                        // Tab 5: Working Hours
                        Tabs\Tab::make(__('custom.shops.tabs.working_hours'))
                            ->schema([
                                Forms\Components\KeyValue::make('working_hours')
                                    ->label(__('custom.shops.working_hours'))
                                    ->keyLabel(__('custom.shops.day'))
                                    ->valueLabel(__('custom.shops.hours'))
                                    ->addActionLabel(__('custom.shops.add_day'))
                                    ->columnSpanFull()
                                    ->helperText(__('custom.shops.working_hours_help')),
                            ]),

                        // Tab 6: Services & Settings
                        Tabs\Tab::make(__('custom.shops.tabs.settings'))
                            ->schema([
                                Section::make(__('custom.shops.sections.services'))
                                    ->schema([
                                        Forms\Components\Select::make('service_ids')
                                            ->label(__('custom.shops.services'))
                                            ->relationship('services', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make(__('custom.shops.sections.settings'))
                                    ->schema([
                                        Forms\Components\Select::make('vendor_id')
                                            ->label(__('custom.shops.vendor'))
                                            ->relationship('vendor', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label(__('custom.shops.is_active'))
                                            ->default(true),

                                        Forms\Components\Toggle::make('is_free_delivery')
                                            ->label(__('custom.shops.is_free_delivery'))
                                            ->default(false),

                                        Forms\Components\Toggle::make('is_default')
                                            ->label(__('custom.shops.is_default'))
                                            ->default(false)
                                            ->helperText(__('custom.shops.is_default_help')),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

