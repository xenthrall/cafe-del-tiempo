<?php

namespace Tequia\System\Filament\Resources\Users\Tables\Columns;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

/**
 * Columnas del modo "columns" de UsersTable (tabla plana clásica), separadas
 * en su propio archivo para no saturar UsersTable con la definición de cada
 * campo — mismo patrón que MovementTableColumns en `finance`.
 */
class UserTableColumns
{
    /**
     * @return array<int, TextColumn|IconColumn>
     */
    public static function make(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            TextColumn::make('email')
                ->label('Correo electrónico')
                ->searchable()
                ->sortable(),

            IconColumn::make('is_admin')
                ->label('Administrador')
                ->boolean(),

            TextColumn::make('created_at')
                ->label('Registrado')
                ->dateTime('d/m/Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
