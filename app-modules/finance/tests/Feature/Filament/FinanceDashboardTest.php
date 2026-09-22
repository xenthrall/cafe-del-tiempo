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

    expect($component->get('totalBalances'))->toBe([
        ['currency' => 'COP', 'formatted' => '$ 150,00'],
    ]);
});

it('sums this month income and expense movements separately', function () {
    $account = Account::factory()->create();

    Movement::factory()->income()->create(['account_id' => $account->id, 'amount' => 200, 'date' => now()]);
    Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 80, 'date' => now()]);
    Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 1000, 'date' => now()->subMonths(2)]);

    $component = Livewire::test(FinanceDashboard::class);

    expect($component->get('periodIncomes'))->toBe([['currency' => 'COP', 'formatted' => '$ 200,00']])
        ->and($component->get('periodExpenses'))->toBe([['currency' => 'COP', 'formatted' => '$ 80,00']])
        ->and($component->get('periodNets'))->toBe([['currency' => 'COP', 'formatted' => '$ 120,00', 'isNegative' => false]]);
});

it('keeps balances of different currencies separate instead of mixing them', function () {
    Account::factory()->create(['currency' => 'COP', 'opening_balance' => 100]);
    Account::factory()->create(['currency' => 'USD', 'opening_balance' => 10]);

    $component = Livewire::test(FinanceDashboard::class);

    expect(collect($component->get('totalBalances'))->pluck('formatted', 'currency')->all())
        ->toBe(['COP' => '$ 100,00', 'USD' => 'US$ 10,00']);
});
