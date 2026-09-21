<?php

use App\Models\User;
use Livewire\Livewire;
use Tequia\Finance\Filament\Pages\FinanceDashboard;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    $this->get(FinanceDashboard::getUrl())->assertSuccessful();
});

it('sums the balance of every account into the total balance', function () {
    Account::factory()->create(['opening_balance' => 100]);
    Account::factory()->create(['opening_balance' => 50]);

    $component = Livewire::test(FinanceDashboard::class);

    expect($component->get('totalBalance'))->toBe('$ 150,00');
});

it('sums this month income and expense movements separately', function () {
    $account = Account::factory()->create();

    Movement::factory()->income()->create(['account_id' => $account->id, 'amount' => 200, 'date' => now()]);
    Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 80, 'date' => now()]);
    Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 1000, 'date' => now()->subMonths(2)]);

    $component = Livewire::test(FinanceDashboard::class);

    expect($component->get('monthIncome'))->toBe('$ 200,00')
        ->and($component->get('monthExpense'))->toBe('$ 80,00')
        ->and($component->get('monthNet'))->toBe('$ 120,00')
        ->and($component->get('monthNetIsNegative'))->toBeFalse();
});
