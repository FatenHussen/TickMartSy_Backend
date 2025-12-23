<?php

namespace App\Filament\Resources\StoreUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StoreUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('store_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
