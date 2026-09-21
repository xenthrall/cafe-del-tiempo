<?php

use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Filament\Resources\Movements\Pages\ManageMovements;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\Movement;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

/**
 * `fillForm()`/`callAction(..., data:)` don't apply their data to a mounted
 * action's schema on this page (the mounted action's `data` stays at its
 * `fillForm()` defaults) — setting each `mountedActions.0.data.*` path
 * directly does. Confirmed with a debug dump before writing these tests.
 *
 * @param  array<string, mixed>  $data
 */
function setMovementFormData(Testable $test, array $data): Testable
{
    foreach ($data as $key => $value) {
        $test->set("mountedActions.0.data.{$key}", $value);
    }

    return $test;
}

it('renders successfully', function () {
    $this->get(ManageMovements::getUrl())->assertSuccessful();
});

it('creates an income movement', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create(['type' => CategoryType::Income]);

    $test = Livewire::test(ManageMovements::class)->mountAction('manageMovement');

    setMovementFormData($test, [
        'type' => 'income',
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => '100',
        'date' => '2026-01-10',
    ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Movement::query()->where('account_id', $account->id)->where('category_id', $category->id)->exists())->toBeTrue();
});

it('creates an expense when the category and context selects are left empty', function () {
    $account = Account::factory()->create();

    $test = Livewire::test(ManageMovements::class)->mountAction('manageMovement');

    setMovementFormData($test, [
        'type' => 'expense',
        'account_id' => $account->id,
        'amount' => '25',
        'date' => '2026-01-10',
    ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $movement = Movement::query()->where('account_id', $account->id)->sole();

    expect($movement->category_id)->toBeNull()
        ->and($movement->financial_context_id)->toBeNull();
});

it('creates a transfer between two accounts', function () {
    $source = Account::factory()->create();
    $destination = Account::factory()->create();

    $test = Livewire::test(ManageMovements::class)->mountAction('manageMovement');

    setMovementFormData($test, [
        'type' => 'transfer',
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
        'amount' => '40',
        'date' => '2026-01-10',
    ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Movement::query()->where('from_account_id', $source->id)->where('to_account_id', $destination->id)->exists())->toBeTrue();
});

it('surfaces a validation error for a transfer to the same account', function () {
    $account = Account::factory()->create();

    $test = Livewire::test(ManageMovements::class)->mountAction('manageMovement');

    setMovementFormData($test, [
        'type' => 'transfer',
        'from_account_id' => $account->id,
        'to_account_id' => $account->id,
        'amount' => '40',
        'date' => '2026-01-10',
    ])
        ->callMountedAction()
        ->assertHasFormErrors(['from_account_id']);

    expect(Movement::query()->where('from_account_id', $account->id)->exists())->toBeFalse();
});

it('updates an existing movement', function () {
    $account = Account::factory()->create();
    $movement = Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 10]);

    $test = Livewire::test(ManageMovements::class)
        ->mountAction(TestAction::make('manageMovement')->arguments(['movement' => $movement->id]));

    setMovementFormData($test, ['amount' => '99'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($movement->fresh()->amount)->toBe('99.00');
});

it('deletes a movement', function () {
    $movement = Movement::factory()->create();

    Livewire::test(ManageMovements::class)
        ->callAction(TestAction::make('deleteMovement')->arguments(['movement' => $movement->id]));

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
