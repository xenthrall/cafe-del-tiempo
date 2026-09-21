<?php

use App\Models\User;
use Livewire\Livewire;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Filament\Resources\Movements\Pages\ManageMovements;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\Movement;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    $this->get(ManageMovements::getUrl())->assertSuccessful();
});

it('creates an income movement', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create(['type' => CategoryType::Income]);

    Livewire::test(ManageMovements::class)
        ->set('type', 'income')
        ->set('accountId', $account->id)
        ->set('categoryId', $category->id)
        ->set('amount', '100')
        ->set('date', '2026-01-10')
        ->call('save')
        ->assertHasNoErrors();

    expect(Movement::query()->where('account_id', $account->id)->where('category_id', $category->id)->exists())->toBeTrue();
});

it('creates an expense when the category and context selects are left empty', function () {
    // The "no category"/"no context" <option> sends an empty string, exactly like
    // an unselected <select> would over the wire — not a PHP null.
    $account = Account::factory()->create();

    Livewire::test(ManageMovements::class)
        ->set('type', 'expense')
        ->set('accountId', $account->id)
        ->set('categoryId', '')
        ->set('financialContextId', '')
        ->set('amount', '25')
        ->set('date', '2026-01-10')
        ->call('save')
        ->assertHasNoErrors();

    $movement = Movement::query()->where('account_id', $account->id)->sole();

    expect($movement->category_id)->toBeNull()
        ->and($movement->financial_context_id)->toBeNull();
});

it('creates a transfer between two accounts', function () {
    $source = Account::factory()->create();
    $destination = Account::factory()->create();

    Livewire::test(ManageMovements::class)
        ->set('type', 'transfer')
        ->set('fromAccountId', $source->id)
        ->set('toAccountId', $destination->id)
        ->set('amount', '40')
        ->set('date', '2026-01-10')
        ->call('save')
        ->assertHasNoErrors();

    expect(Movement::query()->where('from_account_id', $source->id)->where('to_account_id', $destination->id)->exists())->toBeTrue();
});

it('surfaces a validation error for a transfer to the same account', function () {
    $account = Account::factory()->create();

    Livewire::test(ManageMovements::class)
        ->set('type', 'transfer')
        ->set('fromAccountId', $account->id)
        ->set('toAccountId', $account->id)
        ->set('amount', '40')
        ->set('date', '2026-01-10')
        ->call('save')
        ->assertHasErrors(['from_account_id']);
});

it('updates an existing movement', function () {
    $account = Account::factory()->create();
    $movement = Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 10]);

    Livewire::test(ManageMovements::class)
        ->call('openEditModal', $movement->id)
        ->set('amount', '99')
        ->call('save')
        ->assertHasNoErrors();

    expect($movement->fresh()->amount)->toBe('99.00');
});

it('deletes a movement', function () {
    $movement = Movement::factory()->create();

    Livewire::test(ManageMovements::class)->call('delete', $movement->id);

    expect(Movement::query()->find($movement->id))->toBeNull();
});

it('filters the list by movement type', function () {
    Movement::factory()->income()->create(['description' => 'Salario']);
    Movement::factory()->expense()->create(['description' => 'Mercado']);

    Livewire::test(ManageMovements::class)
        ->call('setActiveType', 'income')
        ->assertSee('Salario')
        ->assertDontSee('Mercado');
});
