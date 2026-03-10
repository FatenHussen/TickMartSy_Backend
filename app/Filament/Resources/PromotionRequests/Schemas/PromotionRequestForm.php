<?php

namespace App\Filament\Resources\PromotionRequests\Schemas;

use App\Enums\PromotionType;
use App\Models\Shop;
use App\Services\Vendor\VendorSubscriptionQuotaService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
        $quota = app(VendorSubscriptionQuotaService::class)->getUsageSnapshot($user);
        $canBanner = $quota['has_active'] && $quota['can_create_banner'];
        $remainingCampaigns = $quota['remaining_campaigns'];
        $remainingCampaignsLabel = $remainingCampaigns === null ? __('custom.unlimited') : (string) $remainingCampaigns;

        return $schema->columns(1)->schema([
            Tabs::make('promotion_tabs')
                ->tabs([
                    Tab::make(__('custom.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.basic_information_section'))
                                ->schema([
                                    Forms\Components\Select::make('type')
                                        ->label(__('custom.promotion_type'))
                                        ->options(array_filter([
                                            'offer' => __('custom.offer'),
                                            'banner' => $canBanner ? __('custom.banner') : null,
                                        ]))
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->helperText(__('custom.subscription_remaining_campaigns_hint', [
                                            'count' => $remainingCampaignsLabel,
                                        ]))
                                        ->afterStateUpdated(function ($state, Set $set) {
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
                                        })
                                        ->columnSpan(1),
                                    Forms\Components\Select::make('shop_id')
                                        ->label(__('custom.shop'))
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
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make(__('custom.details_section'))
                                ->schema([
                                    Forms\Components\TextInput::make('title.ar')
                                        ->label(__('custom.title_ar'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('title.en')
                                        ->label(__('custom.title_en'))
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\Textarea::make('description.ar')
                                        ->label(__('custom.description_ar'))
                                        ->rows(3)
                                        ->maxLength(1000)
                                        ->columnSpan(1),

                                    Forms\Components\Textarea::make('description.en')
                                        ->label(__('custom.description_en'))
                                        ->rows(3)
                                        ->maxLength(1000)
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.images'))
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make(__('custom.promotion_images'))
                                ->schema([
                                    Forms\Components\FileUpload::make('images')
                                        ->label(__('custom.upload_images'))
                                        ->image()
                                        ->multiple()
                                        ->maxFiles(5)
                                        ->disk('public')
                                        ->directory('promotion-requests')
                                        ->imageEditor()
                                        ->reorderable()
                                        ->helperText(__('custom.max_5_images'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.offer_details'))
                        ->icon('heroicon-o-tag')
                        ->visible(fn(Get $get) => $get('type') === 'offer')
                        ->schema([
                            Section::make(__('custom.offer_information'))
                                ->schema([
                                    Forms\Components\TextInput::make('discount_percentage')
                                        ->label(__('custom.discount_percentage'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%')
                                        ->required(fn(Get $get) => $get('type') === 'offer')
                                        ->columnSpan(1),

                                    Forms\Components\DatePicker::make('offer_starts_at')
                                        ->label(__('custom.offer_starts_at'))
                                        ->native(false)
                                        ->required(fn(Get $get) => $get('type') === 'offer')
                                        ->minDate(now())
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($state && $get('offer_ends_at') && $state > $get('offer_ends_at')) {
                                                $set('offer_ends_at', null);
                                            }
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\DatePicker::make('offer_ends_at')
                                        ->label(__('custom.offer_ends_at'))
                                        ->native(false)
                                        ->required(fn(Get $get) => $get('type') === 'offer')
                                        ->minDate(fn(Get $get) => $get('offer_starts_at') ?? now())
                                        ->afterOrEqual('offer_starts_at')
                                        ->columnSpan(1),
                                ])
                                ->columns(3)
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.banner_details'))
                        ->icon('heroicon-o-rectangle-stack')
                        ->visible(fn(Get $get) => $get('type') === 'banner')
                        ->schema([
                            Section::make(__('custom.banner_information'))
                                ->schema([
                                    Forms\Components\Select::make('banner_position')
                                        ->label(__('custom.banner_position'))
                                        ->options([
                                            'home_top' => __('custom.home_top'),
                                            'home_middle' => __('custom.home_middle'),
                                            'home_bottom' => __('custom.home_bottom'),
                                            'category_top' => __('custom.category_top'),
                                            'product_sidebar' => __('custom.product_sidebar'),
                                        ])
                                        ->required(fn(Get $get) => $get('type') === 'banner')
                                        ->native(false)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('link_url')
                                        ->label(__('custom.banner_link'))
                                        ->url()
                                        ->placeholder('https://example.com')
                                        ->helperText(__('custom.banner_link_helper'))
                                        ->columnSpan(1),

                                    Forms\Components\DatePicker::make('banner_starts_at')
                                        ->label(__('custom.banner_starts_at'))
                                        ->native(false)
                                        ->required(fn(Get $get) => $get('type') === 'banner')
                                        ->minDate(now())
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($state && $get('banner_ends_at') && $state > $get('banner_ends_at')) {
                                                $set('banner_ends_at', null);
                                            }
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\DatePicker::make('banner_ends_at')
                                        ->label(__('custom.banner_ends_at'))
                                        ->native(false)
                                        ->required(fn(Get $get) => $get('type') === 'banner')
                                        ->minDate(fn(Get $get) => $get('banner_starts_at') ?? now())
                                        ->afterOrEqual('banner_starts_at')
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.order_status'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->visible(fn($record) => $record !== null)
                        ->schema([
                            Section::make(__('custom.admin_notes'))
                                ->schema([
                                    Forms\Components\Placeholder::make('status')
                                        ->label(__('custom.status'))
                                        ->content(fn($record) => match ($record?->status?->value ?? 'pending') {
                                            'pending' => __('custom.pending'),
                                            'approved' => __('custom.approved'),
                                            'rejected' => __('custom.rejected'),
                                            'expired' => __('custom.expired'),
                                            default => __('custom.pending'),
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('approved_at')
                                        ->label(__('custom.approved_at'))
                                        ->content(fn($record) => $record?->approved_at?->format('Y-m-d H:i') ?? '-')
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('approved_by')
                                        ->label(__('custom.approved_by'))
                                        ->content(fn($record) => $record?->approvedBy?->name ?? '-')
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('admin_notes')
                                        ->label(__('custom.admin_notes'))
                                        ->content(fn($record) => $record?->admin_notes ?? __('custom.no_notes'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(3)
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
