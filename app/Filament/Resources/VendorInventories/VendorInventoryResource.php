<?php

namespace App\Filament\Resources\VendorInventories;

use App\Filament\Resources\VendorInventories\Pages\ListVendorInventories;
use App\Filament\Resources\VendorInventories\Tables\VendorInventoriesTable;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorUser;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VendorInventoryResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'vendor';
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.navigation.inventory');
    }

    public static function getModelLabel(): string
    {
        return __('custom.inventory.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.inventory.title');
    }

    public static function table(Table $table): Table
    {
        return VendorInventoriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $inventorySubQuery = ProductVariant::query()
            ->whereColumn('product_variants.product_id', 'products.id')
            ->selectRaw('COALESCE(SUM(product_variants.quantity), 0)');

        return parent::getEloquentQuery()
            ->where('vendor_id', $user->vendor_id)
            ->addSelect(['inventory_total_qty' => $inventorySubQuery])
            ->withCount('variants')
            ->with(['media', 'variants.media'])
            ->orderBy('inventory_total_qty');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorInventories::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
