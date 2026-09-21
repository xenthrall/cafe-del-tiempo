<?php

use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Filament\Resources\Accounts\Pages\ManageAccounts;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

/**
 * `fillForm()`/`callAction(..., data:)` don't apply their data to a mounted
 * action's schema on this page — see ManageMovementsTest::setMovementFormData()
 * for the same trap. Setting each `mountedActions.0.data.*` path directly does.
 *
 * @param  array<string, mixed>  $data
 */
function setAccountFormData(Testable $test, array $data): Testable
{
    foreach ($data as $key => $value) {
        $test->set("mountedActions.0.data.{$key}", $value);
    }

    return $test;
}

it('renders successfully', function () {
    $this->get(ManageAccounts::getUrl())->assertSuccessful();
});

it('creates an account with an opening balance', function () {
    $test = Livewire::test(ManageAccounts::class)->mountAction('manageAccount');

    setAccountFormData($test, [
        'name' => 'Nequi',
        'type' => 'digital_wallet',
        'opening_balance' => '150000',
    ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $account = Account::query()->where('name', 'Nequi')->sole();

    expect($account->type)->toBe(AccountType::DigitalWallet)
        ->and($account->opening_balance)->toBe('150000.00')
        ->and($account->currency)->toBe('COP');
});

it('updates an existing account', function () {
    $account = Account::factory()->create(['name' => 'Efectivo']);

    $test = Livewire::test(ManageAccounts::class)
        ->mountAction(TestAction::make('manageAccount')->arguments(['account' => $account->id]));

    setAccountFormData($test, ['name' => 'Efectivo en casa'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($account->fresh()->name)->toBe('Efectivo en casa');
});

it('deletes an account without movements', function () {
    $account = Account::factory()->create();

    Livewire::test(ManageAccounts::class)
        ->callAction(TestAction::make('deleteAccount')->arguments(['account' => $account->id]));

    expect(Account::query()->find($account->id))->toBeNull();
});

it('refuses to delete an account that has movements', function () {
    $account = Account::factory()->create();
    Movement::factory()->expense()->create(['account_id' => $account->id]);

    Livewire::test(ManageAccounts::class)
        ->callAction(TestAction::make('deleteAccount')->arguments(['account' => $account->id]));

    expect(Account::query()->find($account->id))->not->toBeNull();
});
