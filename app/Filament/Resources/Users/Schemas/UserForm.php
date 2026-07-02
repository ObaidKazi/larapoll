<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;


class UserForm
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
                DateTimePicker::make('email_verified_at')->default(now()),
                Select::make('roles')
                    ->relationship(name: 'roles', titleAttribute: 'name')
                    ->preload(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
            ]);
    }
}
