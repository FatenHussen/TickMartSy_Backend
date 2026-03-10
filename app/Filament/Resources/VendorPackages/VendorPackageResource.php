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

    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('custom.seller_packages_navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('custom.seller_package_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.seller_packages_plural_model_label');
    }

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
