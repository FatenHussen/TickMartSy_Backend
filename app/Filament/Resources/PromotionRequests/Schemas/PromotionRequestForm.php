<?php

namespace App\Filament\Resources\PromotionRequests\Schemas;

use App\Enums\PromotionType;
use App\Models\Shop;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PromotionRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::guard('vendor-user')->user();
        $shopIds = Shop::where('vendor_id', $user?->vendor_id)->pluck('id', 'id')->toArray();

        return $schema
            ->schema([
                Section::make('معلومات أساسية')
                    ->schema([
                        Forms\Components\Select::make('shop_id')
                            ->label('المتجر')
                            ->options(function () use ($shopIds) {
                                return Shop::whereIn('id', array_keys($shopIds))
                                    ->get()
                                    ->mapWithKeys(fn($shop) => [
                                        $shop->id => $shop->getTranslation('name', app()->getLocale())
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Forms\Components\Select::make('type')
                            ->label('نوع الترويج')
                            ->options([
                                'offer' => 'عرض',
                                'banner' => 'بنر إعلاني',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                // Reset fields when type changes
                                if ($state === 'offer') {
                                    $set('banner_position', null);
                                    $set('link_url', null);
                                    $set('banner_starts_at', null);
                                    $set('banner_ends_at', null);
                                } else {
                                    $set('discount_percentage', null);
                                    $set('offer_starts_at', null);
                                    $set('offer_ends_at', null);
                                }
                            }),
                    ])
                    ->columns(2),

                Section::make('التفاصيل')
                    ->schema([
                        Forms\Components\TextInput::make('title.ar')
                            ->label('العنوان (عربي)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title.en')
                            ->label('العنوان (English)')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description.ar')
                            ->label('الوصف (عربي)')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\Textarea::make('description.en')
                            ->label('الوصف (English)')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\FileUpload::make('images')
                            ->label('الصور')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('promotion-requests')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Offer-specific fields
                Section::make('تفاصيل العرض')
                    ->schema([
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('نسبة الخصم (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(fn(Get $get) => $get('type') === 'offer'),

                        Forms\Components\DatePicker::make('offer_starts_at')
                            ->label('تاريخ بداية العرض')
                            ->native(false)
                            ->required(fn(Get $get) => $get('type') === 'offer')
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state && $get('offer_ends_at') && $state > $get('offer_ends_at')) {
                                    $set('offer_ends_at', null);
                                }
                            }),

                        Forms\Components\DatePicker::make('offer_ends_at')
                            ->label('تاريخ نهاية العرض')
                            ->native(false)
                            ->required(fn(Get $get) => $get('type') === 'offer')
                            ->minDate(fn(Get $get) => $get('offer_starts_at') ?? now())
                            ->afterOrEqual('offer_starts_at'),
                    ])
                    ->columns(3)
                    ->visible(fn(Get $get) => $get('type') === 'offer'),

                // Banner-specific fields
                Section::make('تفاصيل البنر الإعلاني')
                    ->schema([
                        Forms\Components\Select::make('banner_position')
                            ->label('موقع البنر')
                            ->options([
                                'home_top' => 'الصفحة الرئيسية - أعلى',
                                'home_middle' => 'الصفحة الرئيسية - وسط',
                                'home_bottom' => 'الصفحة الرئيسية - أسفل',
                                'category_top' => 'صفحة الفئات - أعلى',
                                'product_sidebar' => 'صفحة المنتج - جانبي',
                            ])
                            ->required(fn(Get $get) => $get('type') === 'banner')
                            ->native(false),

                        Forms\Components\TextInput::make('link_url')
                            ->label('رابط البنر')
                            ->url()
                            ->placeholder('https://example.com')
                            ->helperText('الرابط الذي سيتم التوجيه إليه عند النقر على البنر'),

                        Forms\Components\DatePicker::make('banner_starts_at')
                            ->label('تاريخ بداية البنر')
                            ->native(false)
                            ->required(fn(Get $get) => $get('type') === 'banner')
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state && $get('banner_ends_at') && $state > $get('banner_ends_at')) {
                                    $set('banner_ends_at', null);
                                }
                            }),

                        Forms\Components\DatePicker::make('banner_ends_at')
                            ->label('تاريخ نهاية البنر')
                            ->native(false)
                            ->required(fn(Get $get) => $get('type') === 'banner')
                            ->minDate(fn(Get $get) => $get('banner_starts_at') ?? now())
                            ->afterOrEqual('banner_starts_at'),
                    ])
                    ->columns(2)
                    ->visible(fn(Get $get) => $get('type') === 'banner'),

                // Admin notes (read-only for vendors)
                Section::make('ملاحظات الإدارة')
                    ->schema([
                        Forms\Components\Placeholder::make('status')
                            ->label('الحالة')
                            ->content(fn($record) => match ($record?->status?->value ?? 'pending') {
                                'pending' => 'قيد المراجعة',
                                'approved' => 'موافق عليه',
                                'rejected' => 'مرفوض',
                                'expired' => 'منتهي',
                                default => 'قيد المراجعة',
                            }),

                        Forms\Components\Placeholder::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->content(fn($record) => $record?->admin_notes ?? 'لا توجد ملاحظات'),

                        Forms\Components\Placeholder::make('approved_at')
                            ->label('تاريخ الموافقة')
                            ->content(fn($record) => $record?->approved_at?->format('Y-m-d H:i') ?? '-'),

                        Forms\Components\Placeholder::make('approved_by')
                            ->label('تمت الموافقة بواسطة')
                            ->content(fn($record) => $record?->approvedBy?->name ?? '-'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null),
            ]);
    }
}

