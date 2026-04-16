<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\VendorUser;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();
        $vendorId = $user?->vendor_id;

        return $schema->columns(1)->schema([
            Tabs::make('product_tabs')
                ->tabs([
                    // Tab 1: Basic Information
                    Tab::make(__('custom.products.tabs.basic_info'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.products.sections.basic_info'))
                                ->schema([
                                    Forms\Components\TextInput::make('name.ar')
                                        ->label(__('custom.products.form.name_ar'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('name.en')
                                        ->label(__('custom.products.form.name_en'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('model')
                                        ->label(__('custom.products.form.model_number'))
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('sku')
                                        ->label(__('custom.products.form.product_code'))
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('category_id')
                                        ->label(__('custom.products.form.category_label'))
                                        ->relationship('category', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set): void {
                                            if (!static::isRestaurantCategory($state)) {
                                                return;
                                            }

                                            $set('model', null);
                                            $set('sku', null);
                                            $set('barcode', null);
                                            $set('country.ar', null);
                                            $set('country.en', null);
                                            $set('country_id', null);
                                            $set('sale_country_id', null);
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('barcode')
                                        ->label(__('custom.products.form.barcode_label'))
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('brand_id')
                                        ->label(__('custom.products.form.brand_label'))
                                        ->relationship('brand', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('country.ar')
                                        ->label(__('custom.products.form.country_ar'))
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('country.en')
                                        ->label(__( 'custom.products.form.country_en'))
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\Hidden::make('vendor_id')
                                        ->default($vendorId),

                                    Forms\Components\Hidden::make('approval_status')
                                        ->default(fn() => Auth::guard('admin')->check()
                                            ? \App\Enums\ProductApprovalStatus::APPROVED
                                            : \App\Enums\ProductApprovalStatus::PENDING),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make(__('custom.products.sections.description'))
                                ->schema([
                                    Forms\Components\Textarea::make('description.ar')
                                        ->label(__('custom.products.form.description_ar'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('description.en')
                                        ->label(__('custom.products.form.description_en'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Forms\Components\RichEditor::make('full_description.ar')
                                        ->label(__('custom.products.form.full_description_ar'))
                                        ->toolbarButtons(static::richEditorToolbarButtons())
                                        ->fileAttachmentsDisk('public')
                                        ->fileAttachmentsDirectory('product/rich-editor')
                                        ->extraAttributes(['style' => 'min-height: 380px'])
                                        ->columnSpanFull(),

                                    Forms\Components\RichEditor::make('full_description.en')
                                        ->label(__('custom.products.form.full_description_en'))
                                        ->toolbarButtons(static::richEditorToolbarButtons())
                                        ->fileAttachmentsDisk('public')
                                        ->fileAttachmentsDirectory('product/rich-editor')
                                        ->extraAttributes(['style' => 'min-height: 380px'])
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.category_details'))
                                ->schema([
                                    Forms\Components\Repeater::make('categoryDetails')
                                        ->label('')
                                        ->relationship('categoryDetails')
                                        ->schema([
                                            Forms\Components\Select::make('category_detail_id')
                                                ->label(__('custom.products.form.specification'))
                                                ->options(function (callable $get) {
                                                    $categoryId = $get('../../category_id');
                                                    if (!$categoryId) {
                                                        return [];
                                                    }
                                                    return \App\Models\CategoryDetail::where('category_id', $categoryId)
                                                        ->pluck('name', 'id');
                                                })
                                                ->required()
                                                ->searchable()
                                                ->native(false),

                                            Forms\Components\TextInput::make('detail_value.ar')
                                                ->label(__('custom.products.form.value_ar'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_value.en')
                                                ->label(__('custom.products.form.value_en'))
                                                ->maxLength(255),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->addActionLabel(__('custom.products.form.add_detail'))
                                        ->collapsible(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.extra_details'))
                                ->schema([
                                    Forms\Components\Repeater::make('extraDetails')
                                        ->label('')
                                        ->relationship('extraDetails')
                                        ->schema([
                                            Forms\Components\TextInput::make('detail_key.ar')
                                                ->label(__('custom.products.form.title_ar'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_key.en')
                                                ->label(__('custom.products.form.title_en'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_value.ar')
                                                ->label(__('custom.products.form.value_ar'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_value.en')
                                                ->label(__('custom.products.form.value_en'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('price')
                                                ->label(__('custom.products.form.extra_price'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0)
                                                ->suffix(__('custom.currency'))
                                                ->helperText(__('custom.products.form.extra_price_help'))
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->addActionLabel(__('custom.products.form.add_extra_detail'))
                                        ->collapsible(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.bought_with'))
                                ->schema([
                                    Forms\Components\Select::make('bought_with')
                                        ->label(__('custom.products.form.select_products'))
                                        ->multiple()
                                        ->searchable()
                                        ->options(function () use ($vendorId) {
                                            if (!$vendorId) {
                                                return \App\Models\Product::pluck('name', 'id');
                                            }
                                            return \App\Models\Product::where('vendor_id', $vendorId)
                                                ->pluck('name', 'id');
                                        })
                                        ->helperText(__('custom.products.form.bought_with_help'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.discount'))
                                ->schema([
                                    Forms\Components\Select::make('discount_type')
                                        ->label(__('custom.products.form.discount_type'))
                                        ->options([
                                            'none' => __('custom.products.form.no_discount'),
                                            'percentage' => __('custom.products.form.percentage'),
                                            'fixed' => __('custom.products.form.fixed_amount'),
                                        ])
                                        ->default('none')
                                        ->live()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('discount')
                                        ->label(__('custom.products.form.discount_label'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix(fn(callable $get) => $get('discount_type') === 'percentage' ? '%' : '')
                                        ->visible(fn(callable $get) => $get('discount_type') !== 'none')
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make(__('custom.products.sections.pricing'))
                                ->schema([
                                    Forms\Components\TextInput::make('price')
                                        ->label(__('custom.products.form.price_label'))
                                        ->required()
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('cost_price')
                                        ->label(__('custom.products.form.cost_price'))
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->helperText(__('custom.products.form.cost_price_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label(__('custom.products.form.quantity_label'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('unit')
                                        ->label(__('custom.products.form.unit'))
                                        ->placeholder(__('custom.products.form.unit_placeholder'))
                                        ->helperText(__('custom.products.form.unit_help'))
                                        ->maxLength(50)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('warranty_period')
                                        ->label(__('custom.products.form.warranty_period'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix(__('custom.products.form.warranty_months'))
                                        ->helperText(__('custom.products.form.warranty_period_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('stock')
                                        ->label(__('custom.products.form.stock'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->helperText(__('custom.products.form.stock_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('max_purchase_quantity')
                                        ->label(__('custom.products.form.max_purchase_quantity'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->helperText(__('custom.products.form.max_purchase_quantity_help'))
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make(__('custom.products.sections.visibility'))
                                ->schema([
                                    Forms\Components\Toggle::make('is_visible')
                                        ->label(__('custom.products.form.is_visible'))
                                        ->helperText(__('custom.products.form.is_visible_help'))
                                        ->default(true),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.icons_badges'))
                                ->schema([
                                    Forms\Components\Select::make('icon_ids')
                                        ->label(__('custom.products.form.icons'))
                                        ->relationship('icons', 'name')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->helperText(__('custom.products.form.icons_help'))
                                        ->columnSpanFull(),

                                    Forms\Components\Repeater::make('badges')
                                        ->label(__('custom.products.form.badges'))
                                        ->schema([
                                            Forms\Components\Select::make('id')
                                                ->label(__('custom.products.form.badge'))
                                                ->options(\App\Models\Badge::pluck('name', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->distinct()
                                                ->columnSpan(1),

                                            Forms\Components\Select::make('position')
                                                ->label(__('custom.products.form.badge_position'))
                                                ->options([
                                                    'top' => __('custom.products.form.badge_top'),
                                                    'bottom' => __('custom.products.form.badge_bottom'),
                                                ])
                                                ->required()
                                                ->native(false)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->addActionLabel(__('custom.products.form.add_badge'))
                                        ->collapsible()
                                        ->columnSpanFull()
                                        ->afterStateHydrated(function ($component, $state, $record) {
                                            if ($record && $record->badges) {
                                                $badges = $record->badges->map(function ($badge) {
                                                    return [
                                                        'id' => $badge->id,
                                                        'position' => $badge->pivot->position,
                                                    ];
                                                })->toArray();
                                                $component->state($badges);
                                            }
                                        }),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.delivery_settings'))
                                ->schema([
                                    Forms\Components\Toggle::make('is_instant_delivery')
                                        ->label(__('custom.products.form.instant_delivery'))
                                        ->default(false),

                                    Forms\Components\TimePicker::make('time_prepare')
                                        ->label(__('custom.products.form.preparation_time'))
                                        ->seconds(false),

                                    Forms\Components\TextInput::make('delivery_time')
                                        ->label(__('custom.products.form.delivery_time'))
                                        ->placeholder('مثال: 12-48 ساعة')
                                        ->maxLength(100)
                                        ->helperText(__('custom.products.form.delivery_time_help'))
                                        ->hidden(fn (callable $get) => $get('vendor_id') == 1)
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('delivery_time_tikmool')
                                        ->label(__('custom.products.form.delivery_time'))
                                        ->content('12-48 ساعة (تيك مول)')
                                        ->visible(fn (callable $get) => $get('vendor_id') == 1)
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    // Tab 2: Images
                    Tab::make(__('custom.products.tabs.images'))
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make(__('custom.products.sections.thumbnail'))
                                ->schema([
                                    Forms\Components\FileUpload::make('thumbnail')
                                        ->label(__('custom.products.form.thumbnail'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product/thumbnails')
                                        ->imageEditor()
                                        ->helperText(__('custom.products.form.thumbnail_help'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.main_image'))
                                ->schema([
                                    Forms\Components\FileUpload::make('main_image')
                                        ->label(__('custom.products.form.main_image_label'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product')
                                        ->imageEditor()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.images'))
                                ->schema([
                                    Forms\Components\FileUpload::make('media')
                                        ->label(__('custom.products.form.additional_images_label'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product')
                                        ->multiple()
                                        ->minFiles(1)
                                        ->maxFiles(10)
                                        ->required()
                                        ->reorderable()
                                        ->imageEditor()
                                        ->helperText(__('custom.products.form.images_help'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 3: Variants
                    Tab::make(__('custom.products.tabs.variants'))
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Section::make(__('custom.products.variants.title'))
                                ->description(__('custom.products.form.add_variant_description'))
                                ->schema([
                                    Forms\Components\Placeholder::make('variant_info')
                                        ->label('')
                                        ->content(__('custom.products.form.add_variant_placeholder'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.variants'))
                                ->schema([
                                    Forms\Components\Repeater::make('variants')
                                        ->label('')
                                        ->relationship('variants')
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    Forms\Components\TextInput::make('name.ar')
                                                        ->label(__('custom.products.form.variant_name_ar'))
                                                        ->maxLength(255)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('name.en')
                                                        ->label(__('custom.products.form.variant_name_en'))
                                                        ->maxLength(255)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('sku')
                                                        ->label(__('custom.products.form.variant_sku'))
                                                        ->maxLength(255)
                                                        ->unique(table: 'product_variants', column: 'sku', ignoreRecord: true)
                                                        ->columnSpan(1),
                                                ]),

                                            Section::make(__('custom.products.sections.attributes'))
                                                ->schema([
                                                    Forms\Components\Repeater::make('attribute_selections')
                                                        ->label('')
                                                        ->schema([
                                                            Forms\Components\Select::make('attribute_id')
                                                                ->label(__('custom.products.form.attribute_label'))
                                                                ->options(function (callable $get) {
                                                                    $categoryId = $get('../../../../category_id');

                                                                    if (!$categoryId) {
                                                                        return ['_placeholder' => __('custom.products.form.select_category_first')];
                                                                    }

                                                                    $options = \App\Models\CategoryAttribute::where('category_id', $categoryId)
                                                                        ->get()
                                                                        ->pluck('name', 'id')
                                                                        ->toArray();

                                                                    if (empty($options)) {
                                                                        return ['_placeholder' => __('custom.products.form.no_attributes')];
                                                                    }

                                                                    return $options;
                                                                })
                                                                ->live(onBlur: true)
                                                                ->required()
                                                                ->searchable()
                                                                ->afterStateUpdated(function (callable $set) {
                                                                    $set('value_id', null);
                                                                }),

                                                            Forms\Components\Select::make('value_id')
                                                                ->label(__('custom.products.form.value_label'))
                                                                ->options(function (callable $get) {
                                                                    $attributeId = $get('attribute_id');
                                                                    if (!$attributeId || $attributeId === '_placeholder') {
                                                                        return [];
                                                                    }

                                                                    if (static::isColorAttribute($attributeId)) {
                                                                        return static::getColorOptions($attributeId);
                                                                    }

                                                                    $values = \App\Models\AttributeValue::where('category_attribute_id', $attributeId)->get();
                                                                    return $values->mapWithKeys(fn($v) => [$v->id => $v->getTranslation('name', app()->getLocale())])->toArray();
                                                                })
                                                                ->required()
                                                                ->searchable()
                                                                ->native(false)
                                                                ->allowHtml()
                                                                ->extraAttributes(function (callable $get) {
                                                                    return static::isColorAttribute($get('attribute_id'))
                                                                        ? ['class' => 'color-swatch-select']
                                                                        : [];
                                                                }),
                                                        ])
                                                        ->columns(2)
                                                        ->defaultItems(1)
                                                        ->addActionLabel(__('custom.products.form.add_attribute'))
                                                        ->collapsible()
                                                        ->itemLabel(
                                                            fn(array $state): ?string =>
                                                            isset($state['attribute_id']) && isset($state['value_id'])
                                                                ? \App\Models\CategoryAttribute::find($state['attribute_id'])?->name . ': ' . \App\Models\AttributeValue::find($state['value_id'])?->name
                                                                : __('custom.products.form.new_attribute')
                                                        ),

                                                    Forms\Components\Hidden::make('attributes_values_ids')
                                                        ->afterStateHydrated(function ($component, $state, callable $get, callable $set) {
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
                                                ->columnSpanFull()
                                                ->collapsible(),

                                            Forms\Components\Toggle::make('is_trend')
                                                ->label(__('custom.products.form.trending_product'))
                                                ->helperText(__('custom.products.form.trending_help')),

                                            Forms\Components\FileUpload::make('variant_media')
                                                ->label(__('custom.products.form.variant_images'))
                                                ->image()
                                                ->multiple()
                                                ->disk('public')
                                                ->directory('product-variant')
                                                ->maxFiles(5)
                                                ->columnSpanFull(),

                                            Section::make(__('custom.products.sections.shop_availability'))
                                                ->description(__('custom.products.form.variant_availability_description'))
                                                ->schema([
                                                    Forms\Components\Repeater::make('shopVariants')
                                                        ->label('')
                                                        ->relationship('shopVariants')
                                                        ->schema([
                                                            Forms\Components\Select::make('shop_id')
                                                                ->label(__('custom.products.form.shop_name'))
                                                                ->options(function () use ($user) {
                                                                    if (!$user) {
                                                                        return [];
                                                                    }
                                                                    return $user->shops()
                                                                        ->pluck('shops.name', 'shops.id');
                                                                })
                                                                ->required()
                                                                ->searchable()
                                                                ->distinct()
                                                                ->columnSpan(2),

                                                            Forms\Components\TextInput::make('quantity')
                                                                ->label(__('custom.products.form.available_quantity'))
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->required()
                                                                ->suffix(__('custom.products.form.unit'))
                                                                ->helperText(__('custom.products.form.quantity_help')),

                                                            Forms\Components\TextInput::make('price')
                                                                ->label(__('custom.products.form.price_in_shop'))
                                                                ->numeric()
                                                                ->prefix('$')
                                                                ->minValue(0)
                                                                ->required()
                                                                ->helperText(__('custom.products.form.price_help')),
                                                        ])
                                                        ->columns(4)
                                                        ->collapsible()
                                                        ->cloneable()
                                                        ->itemLabel(
                                                            fn(array $state): ?string =>
                                                            isset($state['shop_id']) && $state['shop_id']
                                                                ? __('custom.products.form.shop_with_quantity', [
                                                                    'shop' => \App\Models\Shop::find($state['shop_id'])?->name,
                                                                    'quantity' => $state['quantity'] ?? 0
                                                                ])
                                                                : __('custom.products.form.new_shop')
                                                        )
                                                        ->defaultItems(0)
                                                        ->addActionLabel(__('custom.products.form.add_shop'))
                                                        ->reorderable(false),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible()
                                                ->collapsed(false),
                                        ])
                                        ->columns(1)
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            isset($state['attribute_selections']) && is_array($state['attribute_selections']) && count($state['attribute_selections']) > 0
                                                ? __('custom.products.form.variant_with_attributes', ['count' => count($state['attribute_selections'])])
                                                : __('custom.products.form.new_variant')
                                        )
                                        ->defaultItems(1)
                                        ->addActionLabel(__('custom.products.form.add_new_variant'))
                                        ->reorderableWithButtons(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),

                    // Tab 4: SEO
                    Tab::make(__('custom.products.seo.title'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make(__('custom.products.seo.title'))
                                ->description(__('custom.products.seo.description'))
                                ->schema([
                                    Forms\Components\TextInput::make('seo_title.ar')
                                        ->label(__('custom.products.seo.seo_title_ar'))
                                        ->maxLength(160)
                                        ->helperText(__('custom.products.seo.seo_title_help'))
                                        ->placeholder(__('custom.products.seo.seo_title_placeholder'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('seo_title.en')
                                        ->label(__('custom.products.seo.seo_title_en'))
                                        ->maxLength(160)
                                        ->helperText('Optimal length: 50-60 characters')
                                        ->placeholder('Example: Buy [Product Name] at Best Price')
                                        ->columnSpan(1),

                                    Forms\Components\Textarea::make('seo_description.ar')
                                        ->label(__('custom.products.seo.seo_description_ar'))
                                        ->rows(3)
                                        ->maxLength(320)
                                        ->helperText(__('custom.products.seo.seo_description_help'))
                                        ->placeholder(__('custom.products.seo.seo_description_placeholder'))
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('seo_description.en')
                                        ->label(__('custom.products.seo.seo_description_en'))
                                        ->rows(3)
                                        ->maxLength(320)
                                        ->helperText('Optimal length: 150-160 characters')
                                        ->placeholder('Compelling description that encourages users to click')
                                        ->columnSpanFull(),

                                    Forms\Components\TagsInput::make('seo_keywords.ar')
                                        ->label(__('custom.products.seo.seo_keywords_ar'))
                                        ->placeholder(__('custom.products.seo.seo_keywords_placeholder'))
                                        ->helperText(__('custom.products.seo.seo_keywords_help'))
                                        ->separator(',')
                                        ->columnSpan(1),

                                    Forms\Components\TagsInput::make('seo_keywords.en')
                                        ->label(__('custom.products.seo.seo_keywords_en'))
                                        ->placeholder('Add keyword and press Enter')
                                        ->helperText('Add 5-10 relevant keywords')
                                        ->separator(',')
                                        ->columnSpan(1),

                                    Forms\Components\FileUpload::make('seo_image')
                                        ->label(__('custom.products.seo.seo_image'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product-seo')
                                        ->imageEditor()
                                        ->helperText(__('custom.products.seo.seo_image_help'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function isRestaurantCategory($categoryId): bool
    {
        if (!$categoryId) {
            return false;
        }

        return (bool) \App\Models\Category::query()
            ->whereKey($categoryId)
            ->value('is_restaurant');
    }

    private static function isColorAttribute($attributeId): bool
    {
        if (!$attributeId || $attributeId === '_placeholder') {
            return false;
        }

        return \App\Models\CategoryAttribute::where('id', $attributeId)
            ->value('type') === 'color';
    }

    private static function getAttributeValueHexById(int $valueId): ?string
    {
        $value = \App\Models\AttributeValue::find($valueId);
        if (!$value) {
            return null;
        }

        $hex = $value->getTranslation('name', app()->getLocale(), false);
        if (!$hex) {
            $hex = $value->getTranslation('name', 'en', false)
                ?: $value->getTranslation('name', 'ar', false);
        }

        return is_string($hex) && $hex !== '' ? $hex : null;
    }

    private static function getColorOptions(int $attributeId): array
    {
        $values = \App\Models\AttributeValue::where('category_attribute_id', $attributeId)->get();

        $options = [];
        foreach ($values as $value) {
            $hex = static::getAttributeValueHexById($value->id);
            if (!$hex) {
                continue;
            }
            $options[$value->id] = static::renderColorSwatch($hex);
        }

        return $options;
    }

    private static function renderColorSwatch(string $hex): string
    {
        $hexSafe = e($hex);

        return '<span style="display:inline-flex;align-items:center;gap:8px;">' .
            '<span style="display:inline-block;width:18px;height:18px;border-radius:9999px;background:' . $hexSafe . ';border:1px solid #d1d5db;"></span>' .
            '</span>';
    }

    private static function getAttributeValueIdByHex(int $attributeId, string $hex): ?int
    {
        $query = \App\Models\AttributeValue::where('category_attribute_id', $attributeId)
            ->where(function ($q) use ($hex) {
                $q->where('name->en', $hex)
                    ->orWhere('name->ar', $hex)
                    ->orWhere('name', $hex);
            })
            ->select('id')
            ->first();

        return $query?->id;
    }

    private static function richEditorToolbarButtons(): array
    {
        return [
            'attachFiles',
            'blockquote',
            'bold',
            'bulletList',
            'codeBlock',
            'h2',
            'h3',
            'italic',
            'link',
            'orderedList',
            'redo',
            'strike',
            'underline',
            'undo',
        ];
    }
}
