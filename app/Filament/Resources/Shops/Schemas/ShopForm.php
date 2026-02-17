<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShopForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('custom.shops.sections.basic_info'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('custom.shops.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label(__('custom.shops.description'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('logo')
                            ->label(__('custom.shops.logo'))
                            ->image()
                            ->directory('shops/logos')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

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
                        Forms\Components\Textarea::make('address')
                            ->label(__('custom.shops.address'))
                            ->rows(2)
                            ->columnSpanFull(),

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

                Section::make(__('custom.shops.sections.working_hours'))
                    ->schema([
                        Forms\Components\Repeater::make('working_hours')
                            ->label(__('custom.shops.working_hours'))
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->label(__('custom.shops.day'))
                                    ->options([
                                        'monday' => __('custom.shops.days.monday'),
                                        'tuesday' => __('custom.shops.days.tuesday'),
                                        'wednesday' => __('custom.shops.days.wednesday'),
                                        'thursday' => __('custom.shops.days.thursday'),
                                        'friday' => __('custom.shops.days.friday'),
                                        'saturday' => __('custom.shops.days.saturday'),
                                        'sunday' => __('custom.shops.days.sunday'),
                                    ])
                                    ->required(),

                                Forms\Components\TimePicker::make('open')
                                    ->label(__('custom.shops.open'))
                                    ->required(),

                                Forms\Components\TimePicker::make('close')
                                    ->label(__('custom.shops.close'))
                                    ->required(),

                                Forms\Components\Toggle::make('closed')
                                    ->label(__('custom.shops.closed'))
                                    ->default(false),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ]),

                Section::make(__('custom.shops.sections.settings'))
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('custom.shops.is_active'))
                            ->default(true),

                        Forms\Components\Toggle::make('is_free_delivery')
                            ->label(__('custom.shops.is_free_delivery'))
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }
}
