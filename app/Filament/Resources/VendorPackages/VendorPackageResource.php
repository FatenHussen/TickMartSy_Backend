<?php

namespace App\Filament\Resources\VendorPackages;

use App\Filament\Resources\VendorPackages\Pages;
use App\Models\VendorPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VendorPackageResource extends Resource
{
    protected static ?string $model = VendorPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CubeTransparent;

    protected static ?string $navigationLabel = 'باقات البائعين';

    protected static ?string $modelLabel = 'باقة';

    protected static ?string $pluralModelLabel = 'باقات البائعين';

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\VendorPackages\Schemas\VendorPackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\VendorPackages\Schemas\VendorPackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\VendorPackages\Tables\VendorPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendorPackages::route('/'),
            'view' => Pages\ViewVendorPackage::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Only view packages
    }

    public static function canEdit($record): bool
    {
        return false; // Only view packages
    }

    public static function canDelete($record): bool
    {
        return false; // Only view packages
    }
}
