<?php

namespace App\Filament\Resources\Shops;

use App\Filament\Resources\Shops\Pages;
use App\Filament\Resources\Shops\Schemas\ShopForm;
use App\Filament\Resources\Shops\Schemas\ShopInfolist;
use App\Filament\Resources\Shops\Tables\ShopsTable;
use App\Models\Shop;
use App\Models\VendorUser;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $navigationLabel = 'المتاجر';

    protected static ?string $modelLabel = 'متجر';

    protected static ?string $pluralModelLabel = 'المتاجر';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ShopForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShopInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Get shop IDs from shop_users table
        $shopIds = $user->shops()->pluck('shops.id');

        return parent::getEloquentQuery()->whereIn('id', $shopIds);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
            'view' => Pages\ViewShop::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Vendors can't create shops, only admin can
    }
}
