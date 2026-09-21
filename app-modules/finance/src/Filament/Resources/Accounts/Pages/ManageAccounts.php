<?php

namespace Tequia\Finance\Filament\Resources\Accounts\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Filament\Resources\Accounts\AccountResource;
use Tequia\Finance\Models\Account;

class ManageAccounts extends Page
{
    protected static string $resource = AccountResource::class;

    protected string $view = 'finance::filament.resources.accounts.pages.manage-accounts';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $accounts = [];

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'cash';

    public string $openingBalance = '0';

    public function mount(): void
    {
        $this->refreshAccounts();
    }

    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->type = AccountType::Cash->value;
        $this->openingBalance = '0';
        $this->dispatch('open-modal', id: 'account-form-modal');
    }

    public function openEditModal(int $accountId): void
    {
        $account = Account::findOrFail($accountId);

        $this->editingId = $account->id;
        $this->name = $account->name;
        $this->type = $account->type->value;
        $this->openingBalance = (string) $account->opening_balance;
        $this->dispatch('open-modal', id: 'account-form-modal');
    }

    public function save(): void
    {
        $data = Validator::make(
            [
                'name' => $this->name,
                'type' => $this->type,
                'opening_balance' => $this->openingBalance,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::enum(AccountType::class)],
                'opening_balance' => ['required', 'numeric'],
            ],
        )->validate();

        $data['currency'] = 'COP';

        if ($this->editingId) {
            Account::findOrFail($this->editingId)->update($data);
        } else {
            Account::create($data);
        }

        $this->dispatch('close-modal', id: 'account-form-modal');
        $this->refreshAccounts();
    }

    public function delete(int $accountId): void
    {
        $account = Account::findOrFail($accountId);

        if ($account->hasMovements()) {
            Notification::make()
                ->title('No se puede eliminar')
                ->body('Esta cuenta tiene movimientos registrados. Elimínalos primero si de verdad quieres borrar la cuenta.')
                ->danger()
                ->send();

            return;
        }

        $account->delete();

        $this->refreshAccounts();
    }

    private function refreshAccounts(): void
    {
        $this->accounts = Account::query()
            ->with(['movements', 'outgoingTransfers', 'incomingTransfers'])
            ->orderBy('name')
            ->get()
            ->map(fn (Account $account): array => [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type->value,
                'typeLabel' => $account->type->label(),
                'typeIcon' => $account->type->icon(),
                'openingBalance' => $account->opening_balance,
                'balance' => $account->balance(),
                'formattedBalance' => $account->formattedBalance(),
            ])
            ->all();
    }
}
