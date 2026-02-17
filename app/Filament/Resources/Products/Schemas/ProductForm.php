<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\VendorUser;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();
        $vendorId = $user?->vendor_id;

        return $schema->columns(1)->schema([
            Section::make(__('custom.products.sections.basic_info'))
                ->schema([
                    Forms\Components\TextInput::make('name.ar')
                        ->label(__('custom.products.name_ar'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name.en')
                        ->label(__('custom.products.name_en'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('category_id')
                        ->label(__('custom.products.category'))
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(onBlur: true),

                    Forms\Components\Select::make('brand_id')
                        ->label(__('custom.products.brand'))
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Hidden::make('vendor_id')
                        ->default($vendorId),

                    Forms\Components\Hidden::make('approval_status')
                        ->default(fn () => Auth::guard('admin')->check()
                            ? \App\Enums\ProductApprovalStatus::APPROVED
                            : \App\Enums\ProductApprovalStatus::PENDING),

                    Forms\Components\TextInput::make('sku')
                        ->label(__('custom.products.sku'))
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('barcode')
                        ->label(__('custom.products.barcode'))
                        ->maxLength(255),

                    Forms\Components\TextInput::make('model')
                        ->label(__('custom.products.model'))
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                        Forms\Components\Textarea::make('description.ar')
                        ->label(__('custom.products.description_ar'))
                        ->required()
                        ->rows(3),
                        Forms\Components\Textarea::make('description.en')
                        ->label(__('custom.products.description_en'))
                        ->required()
                        ->rows(3),
                          Forms\Components\Textarea::make('full_description.ar')
                        ->label(__('custom.products.full_description_ar'))
                        ,

                    Forms\Components\Textarea::make('full_description.en')
                        ->label(__('custom.products.full_description_en')),

                ]),

            Section::make(__('custom.products.sections.pricing'))
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label(__('custom.products.price'))
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0),

                    Forms\Components\TextInput::make('discount')
                        ->label(__('custom.products.discount'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),

                    Forms\Components\TextInput::make('quantity')
                        ->label(__('custom.products.quantity_available'))
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\TextInput::make('country.ar')
                        ->label(__('custom.products.country_ar'))
                        ->maxLength(255),

                    Forms\Components\TextInput::make('country.en')
                        ->label(__('custom.products.country_en'))
                        ->maxLength(255),
                ]),

            Section::make('إعدادات التوصيل')
                ->schema([
                    Forms\Components\Toggle::make('is_instant_delivery')
                        ->label(__('custom.products.is_instant_delivery'))
                        ->default(false),

                    Forms\Components\TimePicker::make('time_prepare')
                        ->label(__('custom.products.time_prepare'))
                        ->seconds(false),
                ])
                ->columns(2),

            Section::make(__('custom.products.sections.images'))
                ->schema([
                    Forms\Components\FileUpload::make('media')
                        ->label(__('custom.products.media'))
                        ->image()
                        ->multiple()
                        ->maxFiles(10)
                        ->reorderable()
                        ->imageEditor()
                        ->columnSpanFull(),
                ]),

            Section::make(__('custom.products.sections.bought_with'))
                ->schema([
                    Forms\Components\Select::make('bought_with')
                        ->label(__('custom.products.bought_with'))
                        ->multiple()
                        ->options(function () {
                            return \App\Models\Product::query()
                                ->orderBy('name')
                                ->limit(50)
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),

            Section::make(__('custom.products.sections.variants'))
                ->description(__('custom.products.variants.description'))
                ->schema([
                    Forms\Components\Repeater::make('variants')
                        ->label('')
                        ->relationship('variants')
                        ->schema([
                            Section::make(__('custom.products.variants.attributes.title'))
                                ->description(__('custom.products.variants.attributes.description'))
                                ->schema([
                                    Forms\Components\Repeater::make('attribute_selections')
                                        ->label('')
                                        ->schema([
                                            Forms\Components\Select::make('attribute_id')
                                                ->label(__('custom.products.variants.attributes.attribute'))
                                                ->options(function (callable $get) {
                                                    // Get category_id from the root form level
                                                    $categoryId = $get('../../../../category_id');

                                                    if (!$categoryId) {
                                                        return ['_placeholder' => __('custom.products.variants.attributes.select_category_first')];
                                                    }

                                                    $options = \App\Models\CategoryAttribute::where('category_id', $categoryId)
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();

                                                    if (empty($options)) {
                                                        return ['_placeholder' => __('custom.products.variants.attributes.no_attributes')];
                                                    }

                                                    return $options;
                                                })
                                                ->live(onBlur: true)
                                                ->required()
                                                ->searchable()
                                                ->afterStateUpdated(fn (callable $set) => $set('value_id', null)),

                                            Forms\Components\ViewField::make('value_id')
                                                ->view('filament.forms.color-value-selector')
                                                ->viewData(function (callable $get) {
                                                    $attributeId = $get('attribute_id');
                                                    if (!$attributeId || $attributeId === '_placeholder') {
                                                        return [
                                                            'options' => [],
                                                            'type' => 'text',
                                                        ];
                                                    }

                                                    $attribute = \App\Models\CategoryAttribute::find($attributeId);
                                                    $values = \App\Models\AttributeValue::where('category_attribute_id', $attributeId)->get();

                                                    return [
                                                        'options' => $values->mapWithKeys(fn($v) => [$v->id => $v->name])->toArray(),
                                                        'type' => $attribute?->type ?? 'text',
                                                    ];
                                                }),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(1)
                                        ->addActionLabel('➕ إضافة خاصية')
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string =>
                                            isset($state['attribute_id']) && isset($state['value_id'])
                                                ? \App\Models\CategoryAttribute::find($state['attribute_id'])?->name . ': ' . \App\Models\AttributeValue::find($state['value_id'])?->name
                                                : __('custom.products.variants.attributes.new')
                                        ),

                                    Forms\Components\Hidden::make('attributes_values_ids')
                                        ->afterStateHydrated(function ($component, $state, callable $get, callable $set) {
                                            // Load existing attributes into repeater format
                                            if ($state && is_array($state)) {
                                                $selections = [];
                                                foreach ($state as $valueId) {
                                                    $value = \App\Models\AttributeValue::find($valueId);
                                                    if ($value) {
                                                        $selections[] = [
                                                            'attribute_id' => $value->category_attribute_id,
                                                            'value_id' => $valueId,
                                                        ];
                                                    }
                                                }
                                                $set('attribute_selections', $selections);
                                            }
                                        })
                                        ->dehydrateStateUsing(function ($state, callable $get) {
                                            $selections = $get('attribute_selections');
                                            if (!$selections) {
                                                return [];
                                            }
                                            return collect($selections)->pluck('value_id')->filter()->values()->toArray();
                                        })
                                ])
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('is_trend')
                                ->label(__('custom.products.variants.is_trend'))
                                ->helperText(__('custom.products.variants.is_trend_help')),

                            Forms\Components\FileUpload::make('variant_media')
                                ->label(__('custom.products.variants.variant_media'))
                                ->image()
                                ->multiple()
                                ->maxFiles(5)
                                ->columnSpanFull(),

                            Section::make(__('custom.products.variants.shop_variants.title'))
                                ->description(__('custom.products.variants.shop_variants.description'))
                                ->schema([
                                    Forms\Components\Repeater::make('shopVariants')
                                        ->label('')
                                        ->relationship('shopVariants')
                                        ->schema([
                                            Forms\Components\Select::make('shop_id')
                                                ->label(__('custom.products.variants.shop_variants.shop_name'))
                                                ->options(function () use ($user) {
                                                    if (!$user) {
                                                        return [];
                                                    }
                                                    return $user->shops()
                                                        ->pluck('shops.name', 'shops.id');
                                                })->required()
                                                ->searchable()
                                                ->distinct()
                                                ->columnSpan(2),

                                            Forms\Components\TextInput::make('quantity')
                                                ->label('الكمية المتاحة')
                                                ->numeric()
                                                ->minValue(0)
                                                ->required()
                                                ->suffix(__('custom.products.variants.shop_variants.unit'))
                                                ->helperText(__('custom.products.variants.shop_variants.quantity_help')),

                                            Forms\Components\TextInput::make('price')
                                                ->label(__('custom.products.variants.shop_variants.price'))
                                                ->numeric()
                                                ->prefix('$')
                                                ->minValue(0)
                                                ->required()
                                                ->helperText(__('custom.products.variants.shop_variants.price_help')),
                                        ])
                                        ->columns(4)
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(fn (array $state): ?string =>
                                            isset($state['shop_id']) && $state['shop_id']
                                                ? __('custom.products.variants.shop_variants.with_quantity', [
                                                    'shop' => \App\Models\Shop::find($state['shop_id'])?->name,
                                                    'quantity' => $state['quantity'] ?? 0
                                                ])
                                                : __('custom.products.variants.shop_variants.new')
                                        )
                                        ->defaultItems(0)
                                        ->addActionLabel('➕ إضافة متجر')
                                        ->reorderable(false),
                                ])
                                ->columnSpanFull()
                                ->collapsible()
                                ->collapsed(false),
                        ])
                        ->columns(1)
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(fn (array $state): ?string =>
                            isset($state['attribute_selections']) && is_array($state['attribute_selections']) && count($state['attribute_selections']) > 0
                                ? __('custom.products.variants.with_attributes', ['count' => count($state['attribute_selections'])])
                                : __('custom.products.variants.new')
                        )
                        ->defaultItems(1)
                        ->addActionLabel('➕ إضافة متغير جديد')
                        ->reorderableWithButtons(),
                ])
                ->collapsible()->columnSpanFull(),

        ]);
    }
}

