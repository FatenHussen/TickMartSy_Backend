<?php

namespace App\Filament\Resources\StoreUsers;

use App\Filament\Resources\StoreUsers\Pages\CreateStoreUser;
use App\Filament\Resources\StoreUsers\Pages\EditStoreUser;
use App\Filament\Resources\StoreUsers\Pages\ListStoreUsers;
use App\Filament\Resources\StoreUsers\Pages\ViewStoreUser;
use App\Filament\Resources\StoreUsers\Schemas\StoreUserForm;
use App\Filament\Resources\StoreUsers\Schemas\StoreUserInfolist;
use App\Filament\Resources\StoreUsers\Tables\StoreUsersTable;
use App\Filament\Traits\ResourcePermissions;
use App\Models\StoreUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreUserResource extends Resource
{
    use ResourcePermissions;
    protected static ?string $model = StoreUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'StoreUser';

    public static function form(Schema $schema): Schema
    {
        return StoreUserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StoreUserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoreUsers::route('/'),
            'create' => CreateStoreUser::route('/create'),
            'view' => ViewStoreUser::route('/{record}'),
            'edit' => EditStoreUser::route('/{record}/edit'),
        ];
    }
}
