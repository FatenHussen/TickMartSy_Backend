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
                Section::make('معلومات المتجر')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المتجر')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('logo')
                            ->label('الشعار')
                            ->image()
                            ->directory('shops/logos')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('معلومات التواصل')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('الهاتف')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('mobile')
                            ->label('الموبايل')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('العنوان والموقع')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('lat')
                            ->label('خط العرض')
                            ->numeric(),

                        Forms\Components\TextInput::make('lng')
                            ->label('خط الطول')
                            ->numeric(),

                        Forms\Components\Select::make('area_id')
                            ->label('المنطقة')
                            ->relationship('area', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),

                Section::make('أوقات العمل')
                    ->schema([
                        Forms\Components\Repeater::make('working_hours')
                            ->label('أوقات العمل')
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->label('اليوم')
                                    ->options([
                                        'monday' => 'الإثنين',
                                        'tuesday' => 'الثلاثاء',
                                        'wednesday' => 'الأربعاء',
                                        'thursday' => 'الخميس',
                                        'friday' => 'الجمعة',
                                        'saturday' => 'السبت',
                                        'sunday' => 'الأحد',
                                    ])
                                    ->required(),

                                Forms\Components\TimePicker::make('open')
                                    ->label('وقت الفتح')
                                    ->required(),

                                Forms\Components\TimePicker::make('close')
                                    ->label('وقت الإغلاق')
                                    ->required(),

                                Forms\Components\Toggle::make('closed')
                                    ->label('مغلق')
                                    ->default(false),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ]),

                Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),

                        Forms\Components\Toggle::make('is_free_delivery')
                            ->label('توصيل مجاني')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }
}
