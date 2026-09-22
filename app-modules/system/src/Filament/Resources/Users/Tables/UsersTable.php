<?php

namespace Tequia\System\Filament\Resources\Users\Tables;

use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tequia\System\Filament\Resources\Users\Actions\DeleteUserAction;
use Tequia\System\Filament\Resources\Users\Actions\ManageUserAction;
use Tequia\System\Filament\Resources\Users\Tables\Columns\UserTableColumns;

/**
 * Tabla de usuarios, aparte de la página para no saturarla — solo
 * presentación (columnas, acciones de fila, paginación); la página pasa aquí
 * el `Builder` y el view mode activo. Mismo patrón que
 * `Tequia\Finance\Filament\Resources\Movements\Tables\MovementsTable`.
 *
 * Dos "view modes": `cards` (por defecto) usa una vista Blade custom por
 * registro (`user-card.blade.php`) en vez de columnas de Filament, para
 * control total del diseño; `columns` usa `UserTableColumns`, una tabla
 * plana clásica con `TextColumn`/`IconColumn`.
 */
class UsersTable
{
    public const VIEW_MODE_CARDS = 'cards';

    public const VIEW_MODE_COLUMNS = 'columns';

    public static function configure(Table $table, Builder $query, string $viewMode): Table
    {
        return $table
            ->query($query)
            ->columns(match ($viewMode) {
                self::VIEW_MODE_COLUMNS => UserTableColumns::make(),
                default => [
                    View::make('system::filament.resources.users.tables.user-card'),
                ],
            })
            ->defaultSort('name')
            ->recordActions([
                ManageUserAction::make()->iconButton(),
                DeleteUserAction::make(),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('No hay usuarios todavía')
            ->emptyStateDescription('Registra el primero con el botón "Nuevo usuario".');
    }
}
