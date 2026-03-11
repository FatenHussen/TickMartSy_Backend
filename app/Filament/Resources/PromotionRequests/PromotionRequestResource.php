<?php

namespace App\Filament\Resources\PromotionRequests;

use App\Filament\Resources\PromotionRequests\Pages;
use App\Filament\Resources\PromotionRequests\Tables\PromotionRequestsTable;
use App\Models\PromotionRequest;
use App\Enums\PromotionStatus;
use App\Services\Vendor\VendorSubscriptionQuotaService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PromotionRequestResource extends Resource
{
    protected static ?string $model = PromotionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Megaphone;
    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('custom.promotion_navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('custom.promotion_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.promotion_plural_model_label');
    }

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Get shops that belong to this vendor user
        $shopIds = \App\Models\Shop::where('vendor_id', $user->vendor_id)->pluck('id');

        return parent::getEloquentQuery()
            ->whereIn('shop_id', $shopIds)
            ->with(['shop', 'vendor', 'approvedBy']);
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\PromotionRequests\Schemas\PromotionRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\PromotionRequests\Schemas\PromotionRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotionRequests::route('/'),
            'create' => Pages\CreatePromotionRequest::route('/create'),
            'edit' => Pages\EditPromotionRequest::route('/{record}/edit'),
            'view' => Pages\ViewPromotionRequest::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        // Allow access to the create page - actual quota checks happen in CreatePromotionRequest::mount()
        // This prevents 403 errors and allows proper notification display
        return true;
    }

    public static function canEdit($record): bool
    {
        // Can only edit if pending or rejected
        return $record->status === PromotionStatus::PENDING || $record->status === PromotionStatus::REJECTED;
    }

    public static function canDelete($record): bool
    {
        // Can only delete if pending or rejected
        return $record->status === PromotionStatus::PENDING || $record->status === PromotionStatus::REJECTED;
    }
}
