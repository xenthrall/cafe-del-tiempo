<?php

use App\Models\User;
use Livewire\Livewire;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Filament\Resources\Accounts\Pages\ManageAccounts;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    $this->get(ManageAccounts::getUrl())->assertSuccessful();
});

it('creates an account with an opening balance', function () {
    Livewire::test(ManageAccounts::class)
        ->set('name', 'Nequi')
        ->set('type', 'digital_wallet')
        ->set('openingBalance', '150000')
        ->call('save')
        ->assertHasNoErrors();

    $account = Account::query()->where('name', 'Nequi')->sole();

    expect($account->type)->toBe(AccountType::DigitalWallet)
        ->and($account->opening_balance)->toBe('150000.00')
        ->and($account->currency)->toBe('COP');
});

it('updates an existing account', function () {
    $account = Account::factory()->create(['name' => 'Efectivo']);

    Livewire::test(ManageAccounts::class)
        ->call('openEditModal', $account->id)
        ->set('name', 'Efectivo en casa')
        ->call('save')
        ->assertHasNoErrors();

    expect($account->fresh()->name)->toBe('Efectivo en casa');
});

it('deletes an account without movements', function () {
    $account = Account::factory()->create();

    Livewire::test(ManageAccounts::class)->call('delete', $account->id);

    expect(Account::query()->find($account->id))->toBeNull();
});

it('refuses to delete an account that has movements', function () {
    $account = Account::factory()->create();
    Movement::factory()->expense()->create(['account_id' => $account->id]);

    Livewire::test(ManageAccounts::class)->call('delete', $account->id);

    expect(Account::query()->find($account->id))->not->toBeNull();
});
