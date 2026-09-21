<?php

namespace Tequia\Finance\Filament\Resources\Movements\Tables;

use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tequia\Finance\Filament\Resources\Movements\Actions\DeleteMovementAction;
use Tequia\Finance\Filament\Resources\Movements\Actions\ManageMovementAction;
use Tequia\Finance\Filament\Resources\Movements\Tables\Columns\MovementTableColumns;

/**
 * Tabla de movimientos (ver docs/finance.md — Interfaz Filament), aparte del
 * `Resource`/página para no saturarlos. Solo presentación: columnas, acciones
 * de fila y paginación. El filtrado (tipo, periodo, contexto, categoría) y la
 * query base viven en `ManageMovements`, que pasa aquí el `Builder` ya
 * filtrado — esta clase no sabe nada de filtros.
 *
 * Dos "view modes", alternables desde la UI (`ManageMovements::$viewMode`):
 * `cards` (por defecto) usa una vista Blade custom por registro
 * (`movement-card.blade.php`) en vez de columnas de Filament, para tener
 * control total del diseño responsive; `columns` usa `MovementTableColumns`,
 * una tabla plana clásica con `TextColumn`.
 */
class MovementsTable
{
    public const VIEW_MODE_CARDS = 'cards';

    public const VIEW_MODE_COLUMNS = 'columns';

    public static function configure(Table $table, Builder $query, string $viewMode): Table
    {
        return $table
            ->query($query)
            ->columns(match ($viewMode) {
                self::VIEW_MODE_COLUMNS => MovementTableColumns::make(),
                default => [
                    View::make('finance::filament.resources.movements.tables.movement-card'),
                ],
            })
            ->defaultSort('date', 'desc')
            ->recordActions([
                ManageMovementAction::make()->iconButton(),
                DeleteMovementAction::make(),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->emptyStateHeading('No hay movimientos todavía')
            ->emptyStateDescription('Registra el primero con el botón "Nuevo movimiento", o prueba a quitar algún filtro.');
    }
}
