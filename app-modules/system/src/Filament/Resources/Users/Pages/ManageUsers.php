<?php

namespace Tequia\System\Filament\Resources\Users\Pages;

use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Tequia\System\Filament\Resources\Users\Actions\ManageUserAction;
use Tequia\System\Filament\Resources\Users\Tables\UsersTable;
use Tequia\System\Filament\Resources\Users\UserResource;
use Tequia\System\Filament\Traits\HidesPageHeader;

class ManageUsers extends Page implements HasTable
{
    use HidesPageHeader;
    use InteractsWithTable;

    protected static string $resource = UserResource::class;

    protected string $view = 'system::filament.resources.users.pages.manage-users';

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Usuarios';

    /**
     * Ver UsersTable — alterna entre el listado tipo tarjeta (por defecto,
     * vista Blade custom) y una tabla plana clásica. No se persiste en la URL.
     */
    public string $viewMode = UsersTable::VIEW_MODE_CARDS;

    public function setViewMode(string $viewMode): void
    {
        $this->viewMode = $viewMode;
        $this->resetTable();
    }

    public function manageUserAction(): ManageUserAction
    {
        return ManageUserAction::make();
    }

    public function table(Table $table): Table
    {
        return UsersTable::configure($table, User::query(), $this->viewMode);
    }
}
