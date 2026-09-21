<?php

namespace Tequia\Finance\Filament\Resources\Movements\Tables\Columns;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Movement;

/**
 * Columnas del modo "columns" de MovementsTable (tabla plana clásica),
 * separadas en su propio archivo para no saturar MovementsTable con la
 * definición de cada campo.
 */
class MovementTableColumns
{
    /**
     * @return array<int, TextColumn>
     */
    public static function make(): array
    {
        return [
            TextColumn::make('type')
                ->label('Tipo')
                ->badge()
                ->formatStateUsing(fn (MovementType $state): string => $state->label())
                ->color(fn (MovementType $state): string => $state->color())
                ->icon(fn (MovementType $state): string => $state->icon()),

            TextColumn::make('accountsLabel')
                ->label('Cuenta(s)')
                ->getStateUsing(fn (Movement $record): string => $record->accountsLabel()),

            TextColumn::make('category.name')
                ->label('Categoría')
                ->placeholder('—')
                ->toggleable(),

            TextColumn::make('financialContext.name')
                ->label('Contexto')
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('amount')
                ->label('Monto')
                ->getStateUsing(fn (Movement $record): string => $record->formattedAmount())
                ->weight(FontWeight::SemiBold)
                ->color(fn (Movement $record): string => self::amountColor($record))
                ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('amount', $direction))
                ->alignEnd(),

            TextColumn::make('date')
                ->label('Fecha')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('description')
                ->label('Descripción')
                ->placeholder('—')
                ->limit(40)
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    private static function amountColor(Movement $record): string
    {
        return match (true) {
            $record->type === MovementType::Expense => 'danger',
            $record->type === MovementType::Adjustment && $record->amount < 0 => 'danger',
            $record->type === MovementType::Transfer => 'gray',
            default => 'success',
        };
    }
}
