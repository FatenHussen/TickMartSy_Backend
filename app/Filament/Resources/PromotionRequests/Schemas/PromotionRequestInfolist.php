<?php

namespace App\Filament\Resources\PromotionRequests\Schemas;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PromotionRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Tabs::make('promotion_info_tabs')
                ->tabs([

                    Tab::make(__('custom.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.general_info'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('shop.name')
                                        ->label(__('custom.shop'))
                                        ->formatStateUsing(fn($record) => $record->shop?->getTranslation('name', app()->getLocale()) ?? '-')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-building-storefront')
                                        ->size('lg')
                                        ->weight('bold'),

                                    Infolists\Components\TextEntry::make('type')
                                        ->label(__('custom.promotion_type'))
                                        ->badge()
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            PromotionType::OFFER => __('custom.offer'),
                                            PromotionType::BANNER => __('custom.banner'),
                                            default => $state->value ?? $state,
                                        })
                                        ->color(fn($state) => match ($state) {
                                            PromotionType::OFFER => 'success',
                                            PromotionType::BANNER => 'info',
                                            default => 'gray',
                                        })
                                        ->size('lg'),

                                    Infolists\Components\TextEntry::make('status')
                                        ->label(__('custom.status'))
                                        ->badge()
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            PromotionStatus::PENDING => __('custom.pending'),
                                            PromotionStatus::APPROVED => __('custom.approved'),
                                            PromotionStatus::REJECTED => __('custom.rejected'),
                                            PromotionStatus::EXPIRED => __('custom.expired'),
                                            default => $state->value ?? $state,
                                        })
                                        ->color(fn($state) => match ($state) {
                                            PromotionStatus::PENDING => 'warning',
                                            PromotionStatus::APPROVED => 'success',
                                            PromotionStatus::REJECTED => 'danger',
                                            PromotionStatus::EXPIRED => 'gray',
                                            default => 'gray',
                                        })
                                        ->size('lg'),
                                ])
                                ->columns(3)
                                ->collapsible(),

                            Section::make(__('custom.details'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('title')
                                        ->label(__('custom.title'))
                                        ->formatStateUsing(fn($record) => $record->getTranslation('title', app()->getLocale()))
                                        ->weight('bold')
                                        ->size('lg')
                                        ->color('primary')
                                        ->columnSpanFull(),

                                    Infolists\Components\TextEntry::make('description')
                                        ->label(__('custom.description'))
                                        ->formatStateUsing(fn($record) => $record->getTranslation('description', app()->getLocale()) ?: '-')
                                        ->columnSpanFull()
                                        ->prose(),
                                ])
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.images'))
                        ->icon('heroicon-o-photo')
                        ->badge(fn($record) => !empty($record->images) ? count($record->images) : null)
                        ->schema([
                            Section::make(__('custom.promotion_images'))
                                ->schema([
                                    Infolists\Components\ImageEntry::make('images')
                                        ->label('')
                                        ->disk('public')
                                        ->columnSpanFull()
                                        ->height(200)
                                        ->extraAttributes(['class' => 'rounded-xl'])
                                        ->visible(fn($record) => !empty($record->images)),

                                    Infolists\Components\TextEntry::make('no_images')
                                        ->label('')
                                        ->default(__('custom.no_images'))
                                        ->color('gray')
                                        ->icon('heroicon-o-photo')
                                        ->columnSpanFull()
                                        ->visible(fn($record) => empty($record->images)),
                                ])
                                ->collapsible(false),
                        ]),

                    Tab::make(__('custom.offer_details'))
                        ->icon('heroicon-o-tag')
                        ->visible(fn($record) => $record->type === PromotionType::OFFER)
                        ->schema([
                            Section::make(__('custom.offer_information'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('discount_percentage')
                                        ->label(__('custom.discount_percentage'))
                                        ->suffix('%')
                                        ->badge()
                                        ->color('danger')
                                        ->size('xl')
                                        ->weight('bold')
                                        ->icon('heroicon-o-receipt-percent'),

                                    Infolists\Components\TextEntry::make('offer_starts_at')
                                        ->label(__('custom.offer_starts_at'))
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-calendar'),

                                    Infolists\Components\TextEntry::make('offer_ends_at')
                                        ->label(__('custom.offer_ends_at'))
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->icon(fn($record) => $record->isExpired() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),

                                ])
                                ->columns(4)
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.banner_details'))
                        ->icon('heroicon-o-rectangle-stack')
                        ->visible(fn($record) => $record->type === PromotionType::BANNER)
                        ->schema([
                            Section::make(__('custom.banner_information'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('banner_position')
                                        ->label(__('custom.banner_position'))
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'home_top' => __('custom.home_top'),
                                            'home_middle' => __('custom.home_middle'),
                                            'home_bottom' => __('custom.home_bottom'),
                                            'category_top' => __('custom.category_top'),
                                            'product_sidebar' => __('custom.product_sidebar'),
                                            default => $state,
                                        })
                                        ->badge()
                                        ->color('info')
                                        ->size('lg')
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('link_url')
                                        ->label(__('custom.banner_link'))
                                        ->url(fn($state) => $state)
                                        ->openUrlInNewTab()
                                        ->placeholder(__('custom.no_link'))
                                        ->icon('heroicon-o-link')
                                        ->color('primary')
                                        ->copyable()
                                        ->copyMessage(__('custom.link_copied'))
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('banner_starts_at')
                                        ->label(__('custom.banner_starts_at'))
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-calendar'),

                                    Infolists\Components\TextEntry::make('banner_ends_at')
                                        ->label(__('custom.banner_ends_at'))
                                        ->date('Y-m-d')
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->icon(fn($record) => $record->isExpired() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),

                                    Infolists\Components\TextEntry::make('duration')
                                        ->label(__('custom.duration'))
                                        ->formatStateUsing(function ($record) {
                                            if (!$record->banner_starts_at || !$record->banner_ends_at) return '-';
                                            $days = $record->banner_starts_at->diffInDays($record->banner_ends_at);
                                            return $days . ' ' . __('custom.days');
                                        })
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-clock'),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make(__('custom.banner_status'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('is_expired')
                                        ->label(__('custom.banner_status'))
                                        ->formatStateUsing(fn($record) => $record->isExpired() ? __('custom.expired') : __('custom.active'))
                                        ->badge()
                                        ->color(fn($record) => $record->isExpired() ? 'danger' : 'success')
                                        ->size('lg'),
                                ])
                                ->collapsible(),
                        ]),

                    Tab::make(__('custom.approval_info'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->badge(fn($record) => $record->status !== PromotionStatus::PENDING ? '✓' : null)
                        ->schema([
                            Section::make(__('custom.approval_details'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('admin_notes')
                                        ->label(__('custom.admin_notes'))
                                        ->placeholder(__('custom.no_notes'))
                                        ->columnSpanFull()
                                        ->prose()
                                        ->color(fn($record) => $record->status === PromotionStatus::REJECTED ? 'danger' : 'gray'),

                                    Infolists\Components\TextEntry::make('approved_at')
                                        ->label(__('custom.approved_at'))
                                        ->dateTime('Y-m-d H:i')
                                        ->placeholder('-')
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-calendar')
                                        ->visible(fn($record) => $record->approved_at !== null),

                                    Infolists\Components\TextEntry::make('approvedBy.name')
                                        ->label(__('custom.approved_by'))
                                        ->placeholder('-')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-user')
                                        ->visible(fn($record) => $record->approved_by !== null),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->visible(fn($record) => $record->status !== PromotionStatus::PENDING),

                            Section::make(__('custom.pending_message'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('pending_message')
                                        ->label('')
                                        ->default(__('custom.pending_message'))
                                        ->color('warning')
                                        ->size('lg')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn($record) => $record->status === PromotionStatus::PENDING),
                        ]),

                    Tab::make(__('custom.system_info'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.dates'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label(__('custom.created_at'))
                                        ->dateTime('Y-m-d H:i')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-calendar')
                                        ->since(),

                                    Infolists\Components\TextEntry::make('updated_at')
                                        ->label(__('custom.updated_at'))
                                        ->dateTime('Y-m-d H:i')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-clock')
                                        ->since(),

                                    Infolists\Components\TextEntry::make('id')
                                        ->label(__('custom.request_id'))
                                        ->badge()
                                        ->color('info')
                                        ->copyable()
                                        ->copyMessage(__('custom.id_copied')),

                                    Infolists\Components\TextEntry::make('vendor.name')
                                        ->label(__('custom.vendor'))
                                        ->badge()
                                        ->color('warning')
                                        ->icon('heroicon-o-building-office'),
                                ])
                                ->columns(4)
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
