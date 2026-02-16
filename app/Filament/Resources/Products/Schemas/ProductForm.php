<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\VendorUser;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();
        $vendorId = $user?->vendor_id;

        return $schema->schema([
            Forms\Components\Section::make('المعلومات الأساسية')
                ->schema([
                    Forms\Components\TextInput::make('name.ar')
                        ->label('اسم المنتج (عربي)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name.en')
                        ->label('اسم المنتج (إنجليزي)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('category_id')
                        ->label('الفئة')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive(),

                    Forms\Components\Select::make('brand_id')
                        ->label('العلامة التجارية')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Hidden::make('vendor_id')
                        ->default($vendorId),

                    Forms\Components\TextInput::make('sku')
                        ->label('رمز المنتج (SKU)')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('barcode')
                        ->label('الباركود')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('model')
                        ->label('الموديل')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ])
                ->columns(2),

            Forms\Components\Section::make('الوصف')
                ->schema([
                    Forms\Components\Textarea::make('description.ar')
                        ->label('الوصف المختصر (عربي)')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description.en')
                        ->label('الوصف المختصر (إنجليزي)')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('full_description.ar')
                        ->label('الوصف الكامل (عربي)')
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('full_description.en')
                        ->label('الوصف الكامل (إنجليزي)')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('الأسعار والكميات')
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('السعر')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0),

                    Forms\Components\TextInput::make('discount')
                        ->label('نسبة الخصم (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),

                    Forms\Components\TextInput::make('quantity')
                        ->label('الكمية المتاحة')
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\TextInput::make('country.ar')
                        ->label('بلد المنشأ (عربي)')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('country.en')
                        ->label('بلد المنشأ (إنجليزي)')
                        ->maxLength(255),
                ])
                ->columns(3),

            Forms\Components\Section::make('إعدادات التوصيل')
                ->schema([
                    Forms\Components\Toggle::make('is_instant_delivery')
                        ->label('توصيل فوري')
                        ->default(false),

                    Forms\Components\TimePicker::make('time_prepare')
                        ->label('وقت التحضير')
                        ->seconds(false),
                ])
                ->columns(2),

            Forms\Components\Section::make('الصور')
                ->schema([
                    Forms\Components\FileUpload::make('media')
                        ->label('صور المنتج')
                        ->image()
                        ->multiple()
                        ->maxFiles(10)
                        ->reorderable()
                        ->imageEditor()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('المنتجات المشتراة معاً')
                ->schema([
                    Forms\Components\Select::make('bought_with')
                        ->label('المنتجات المقترحة')
                        ->multiple()
                        ->relationship('boughtWithProducts', 'name')
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('المتغيرات (Variants)')
                ->schema([
                    Forms\Components\Repeater::make('variants')
                        ->label('متغيرات المنتج')
                        ->relationship('variants')
                        ->schema([
                            Forms\Components\Select::make('attributes_values_ids')
                                ->label('قيم الخصائص')
                                ->multiple()
                                ->options(function () {
                                    return \App\Models\AttributeValue::all()
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('is_trend')
                                ->label('رائج'),

                            Forms\Components\FileUpload::make('variant_media')
                                ->label('صور المتغير')
                                ->image()
                                ->multiple()
                                ->maxFiles(5)
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('shopVariants')
                                ->label('توفر المتغير في المتاجر')
                                ->relationship('shopVariants')
                                ->schema([
                                    Forms\Components\Select::make('shop_id')
                                        ->label('المتجر')
                                        ->options(function () use ($user) {
                                            if (!$user) {
                                                return [];
                                            }
                                            return $user->shops()
                                                ->pluck('shops.name', 'shops.id');
                                        })
                                        ->required()
                                        ->searchable(),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label('الكمية')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required(),

                                    Forms\Components\TextInput::make('price')
                                        ->label('السعر')
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->required(),
                                ])
                                ->columns(3)
                                ->collapsible()
                                ->defaultItems(0),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->defaultItems(1),
                ]),
        ]);
    }
}
