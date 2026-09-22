<?php

namespace Tequia\System\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->maxLength(255)
                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                        ? 'Déjalo en blanco para no cambiar la contraseña actual.'
                        : null),
                Toggle::make('is_admin')
                    ->label('Administrador del sistema')
                    ->helperText('Puede entrar al panel de administración (/system).')
                    ->default(false),
            ]);
    }
}
