<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MultiSelect;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;
use Filament\Forms\Components\Hidden;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

            MultiSelect::make('permissions')
                ->label('Permissions')
                ->relationship('permissions', 'name'),

            Hidden::make('guard_name')
                ->default('admin'),
        ]);
    }
}
