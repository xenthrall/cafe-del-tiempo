<?php

namespace Tequia\Finance\Filament\Resources\Accounts\Pages;

use Filament\Resources\Pages\Page;
use Tequia\Finance\Filament\Resources\Accounts\AccountResource;
use Tequia\Finance\Filament\Resources\Accounts\Actions\DeleteAccountAction;
use Tequia\Finance\Filament\Resources\Accounts\Actions\ManageAccountAction;
use Tequia\Finance\Filament\Traits\HidesPageHeader;
use Tequia\Finance\Models\Account;

class ManageAccounts extends Page
{
    use HidesPageHeader;

    protected static string $resource = AccountResource::class;

    protected string $view = 'finance::filament.resources.accounts.pages.manage-accounts';

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Cuentas';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $accounts = [];

    public function mount(): void
    {
        $this->refreshAccounts();
    }

    public function manageAccountAction(): ManageAccountAction
    {
        return ManageAccountAction::make()->after(fn () => $this->refreshAccounts());
    }

    public function deleteAccountAction(): DeleteAccountAction
    {
        return DeleteAccountAction::make()->after(fn () => $this->refreshAccounts());
    }

    public function toggleAccountActive(int $accountId): void
    {
        $account = Account::findOrFail($accountId);
        $account->update(['is_active' => ! $account->is_active]);

        $this->refreshAccounts();
    }

    private function refreshAccounts(): void
    {
        $this->accounts = Account::query()
            ->with(['movements', 'outgoingTransfers', 'incomingTransfers'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (Account $account): array => [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type->value,
                'typeLabel' => $account->type->label(),
                'typeIcon' => $account->type->icon(),
                'balance' => $account->balance(),
                'formattedBalance' => $account->formattedBalance(),
                'isActive' => $account->is_active,
            ])
            ->all();
    }
}
