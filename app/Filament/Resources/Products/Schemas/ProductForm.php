<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\VendorUser;
use Filament\Forms;
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
                    // Tab 1: المعلومات الأساسية
                    Tab::make('المعلومات الأساسية')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('المعلومات العامة')
                                ->schema([
                                    Forms\Components\TextInput::make('name.ar')
                                        ->label('الاسم بالعربي')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('name.en')
                                        ->label('الاسم بالانجليزي')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('model')
                                        ->label('رقم الموديل')
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('sku')
                                        ->label('رقم المنتج')
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('category_id')
                                        ->label('الصنف')
                                        ->relationship('category', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live(onBlur: true)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('barcode')
                                        ->label('الباركود')
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('brand_id')
                                        ->label('العلامة التجارية')
                                        ->relationship('brand', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('country.ar')
                                        ->label('بلد المنتج (عربي)')
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('country.en')
                                        ->label('بلد المنتج (انجليزي)')
                                        ->maxLength(255)
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

                            Section::make('الوصف')
                                ->schema([
                                    Forms\Components\Textarea::make('description.ar')
                                        ->label('الوصف المختصر (عربي)')
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('description.en')
                                        ->label('الوصف المختصر (انجليزي)')
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Forms\Components\RichEditor::make('full_description.ar')
                                        ->label('الوصف الكامل (عربي)')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'underline',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),

                                    Forms\Components\RichEditor::make('full_description.en')
                                        ->label('الوصف الكامل (انجليزي)')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'underline',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),


                            Section::make('الخصم')
                                ->schema([
                                    Forms\Components\Select::make('discount_type')
                                        ->label('نوع الخصم')
                                        ->options([
                                            'none' => 'لا يوجد خصم',
                                            'percentage' => 'نسبة مئوية',
                                            'fixed' => 'مبلغ ثابت',
                                        ])
                                        ->default('none')
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('discount')
                                        ->label('الخصم')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%')
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('السعر والكمية')
                                ->schema([
                                    Forms\Components\TextInput::make('price')
                                        ->label('السعر')
                                        ->required()
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label('الكمية المتاحة')
                                        ->numeric()
                                        ->minValue(0)
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('إعدادات التوصيل')
                                ->schema([
                                    Forms\Components\Toggle::make('is_instant_delivery')
                                        ->label('توصيل فوري')
                                        ->default(false),

                                    Forms\Components\TimePicker::make('time_prepare')
                                        ->label('وقت التحضير')
                                        ->seconds(false),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    // Tab 2: الصور
                    Tab::make('الصور')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make('الصورة الرئيسية')
                                ->schema([
                                    Forms\Components\FileUpload::make('main_image')
                                        ->label('اسحب وأفلت أو انقر لتحميل الصورة الرئيسية')
                                        ->image()
                                        ->disk('public')
                                        ->directory('product')
                                        ->imageEditor()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make('صور إضافية')
                                ->schema([
                                    Forms\Components\FileUpload::make('media')
                                        ->label('اسحب وأفلت أو انقر لتحميل صور إضافية')
                                        ->image()
                                        ->disk('public')
                                        ->directory('product')
                                        ->multiple()
                                        ->maxFiles(10)
                                        ->reorderable()
                                        ->imageEditor()
                                        ->helperText('استبدال جميع الصور القديمة بالصور الجديدة فقط. سيتم الاحتفاظ بالصور القديمة وإضافة الصور الجديدة إليها')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 3: المتغيرات
                    Tab::make('المتغيرات')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Section::make('إضافة متغير جديد')
                                ->description('يرجى اختيار الخصائص والقيم لإنشاء متغير جديد')
                                ->schema([
                                    Forms\Components\Placeholder::make('variant_info')
                                        ->label('')
                                        ->content('إضافة متغير جديد')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make('متغيرات المنتج')
                                ->schema([
                                    Forms\Components\Repeater::make('variants')
                                        ->label('')
                                        ->relationship('variants')
                                        ->schema([
                                            Section::make('المعلومات الأساسية')
                                                ->schema([
                                                    Forms\Components\TextInput::make('variant_sku')
                                                        ->label('رقم الخصم (SKU)')
                                                        ->maxLength(255)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('variant_barcode')
                                                        ->label('رمز SKU مساوية')
                                                        ->maxLength(255)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('variant_price')
                                                        ->label('السعر')
                                                        ->numeric()
                                                        ->prefix('$')
                                                        ->minValue(0)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('variant_discount')
                                                        ->label('الخصم')
                                                        ->numeric()
                                                        ->suffix('%')
                                                        ->minValue(0)
                                                        ->maxValue(100)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('variant_cost')
                                                        ->label('سعر التكلفة')
                                                        ->numeric()
                                                        ->prefix('$')
                                                        ->minValue(0)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('variant_weight')
                                                        ->label('الوزن (كلغ)')
                                                        ->numeric()
                                                        ->suffix('كلغ')
                                                        ->minValue(0)
                                                        ->columnSpan(1),
                                                ])
                                                ->columns(3)
                                                ->collapsible(),

                                            Section::make('الخصائص')
                                                ->schema([
                                                    Forms\Components\Repeater::make('attribute_selections')
                                                        ->label('')
                                                        ->schema([
                                                            Forms\Components\Select::make('attribute_id')
                                                                ->label('الخاصية')
                                                                ->options(function (callable $get) {
                                                                    $categoryId = $get('../../../../category_id');

                                                                    if (!$categoryId) {
                                                                        return ['_placeholder' => 'اختر الفئة أولاً'];
                                                                    }

                                                                    $options = \App\Models\CategoryAttribute::where('category_id', $categoryId)
                                                                        ->get()
                                                                        ->pluck('name', 'id')
                                                                        ->toArray();

                                                                    if (empty($options)) {
                                                                        return ['_placeholder' => 'لا توجد خصائص'];
                                                                    }

                                                                    return $options;
                                                                })
                                                                ->live(onBlur: true)
                                                                ->required()
                                                                ->searchable()
                                                                ->afterStateUpdated(fn(callable $set) => $set('value_id', null)),

                                                            Forms\Components\Select::make('value_id')
                                                                ->label('القيمة')
                                                                ->options(function (callable $get) {
                                                                    $attributeId = $get('attribute_id');
                                                                    if (!$attributeId || $attributeId === '_placeholder') {
                                                                        return [];
                                                                    }

                                                                    $values = \App\Models\AttributeValue::where('category_attribute_id', $attributeId)->get();
                                                                    return $values->mapWithKeys(fn($v) => [$v->id => $v->getTranslation('name', app()->getLocale())])->toArray();
                                                                })
                                                                ->required()
                                                                ->searchable()
                                                                ->native(false),
                                                        ])
                                                        ->columns(2)
                                                        ->defaultItems(1)
                                                        ->addActionLabel('➕ إضافة خاصية')
                                                        ->collapsible()
                                                        ->itemLabel(
                                                            fn(array $state): ?string =>
                                                            isset($state['attribute_id']) && isset($state['value_id'])
                                                                ? \App\Models\CategoryAttribute::find($state['attribute_id'])?->name . ': ' . \App\Models\AttributeValue::find($state['value_id'])?->name
                                                                : 'خاصية جديدة'
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
                                                ->label('منتج رائج')
                                                ->helperText('هل هذا المتغير رائج؟'),

                                            Forms\Components\FileUpload::make('variant_media')
                                                ->label('صور المتغير')
                                                ->image()
                                                ->multiple()
                                                ->disk('public')
                                                ->directory('product-variant')
                                                ->maxFiles(5)
                                                ->columnSpanFull(),

                                            Section::make('توفر المتغير في المتاجر')
                                                ->description('حدد المتاجر التي يتوفر فيها هذا المتغير')
                                                ->schema([
                                                    Forms\Components\Repeater::make('shopVariants')
                                                        ->label('')
                                                        ->relationship('shopVariants')
                                                        ->schema([
                                                            Forms\Components\Select::make('shop_id')
                                                                ->label('اسم المتجر')
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
                                                                ->label('الكمية المتاحة')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->required()
                                                                ->suffix('وحدة')
                                                                ->helperText('الكمية المتوفرة في هذا المتجر'),

                                                            Forms\Components\TextInput::make('price')
                                                                ->label('السعر')
                                                                ->numeric()
                                                                ->prefix('$')
                                                                ->minValue(0)
                                                                ->required()
                                                                ->helperText('سعر المنتج في هذا المتجر'),
                                                        ])
                                                        ->columns(4)
                                                        ->collapsible()
                                                        ->cloneable()
                                                        ->itemLabel(
                                                            fn(array $state): ?string =>
                                                            isset($state['shop_id']) && $state['shop_id']
                                                                ? 'متجر: ' . \App\Models\Shop::find($state['shop_id'])?->name . ' - الكمية: ' . ($state['quantity'] ?? 0)
                                                                : 'متجر جديد'
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
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            isset($state['attribute_selections']) && is_array($state['attribute_selections']) && count($state['attribute_selections']) > 0
                                                ? 'متغير مع ' . count($state['attribute_selections']) . ' خصائص'
                                                : 'متغير جديد'
                                        )
                                        ->defaultItems(1)
                                        ->addActionLabel('➕ إضافة متغير جديد')
                                        ->reorderableWithButtons(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),

                    // Tab 4: تحسين محركات البحث
                    Tab::make('تحسين محركات البحث')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make('معلومات تحسين محركات البحث (SEO)')
                                ->schema([
                                    Forms\Components\TextInput::make('seo_title')
                                        ->label('عنوان السيو')
                                        ->maxLength(160)
                                        ->helperText('الطول المثالي: 50-60 حرف')
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('seo_description')
                                        ->label('وصف السيو')
                                        ->rows(3)
                                        ->maxLength(320)
                                        ->helperText('الطول المثالي: 150-160 حرف')
                                        ->columnSpanFull(),

                                    Forms\Components\TagsInput::make('seo_keywords')
                                        ->label('الكلمات المفتاحية')
                                        ->placeholder('أضف كلمة مفتاحية')
                                        ->helperText('أضف الكلمات المفتاحية المتعلقة بالمنتج')
                                        ->columnSpanFull(),

                                    Forms\Components\FileUpload::make('seo_image')
                                        ->label('صورة السيو')
                                        ->image()
                                        ->disk('public')
                                        ->directory('product-seo')
                                        ->helperText('اسحب وأفلت أو انقر لتحميل صورة السيو')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
