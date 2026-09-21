<?php

use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Filament\Resources\FinancialContexts\Pages\ManageFinancialContexts;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
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
function setContextFormData(Testable $test, array $data): Testable
{
    foreach ($data as $key => $value) {
        $test->set("mountedActions.0.data.{$key}", $value);
    }

    return $test;
}

it('renders successfully', function () {
    $this->get(ManageFinancialContexts::getUrl())->assertSuccessful();
});

it('creates a financial context', function () {
    $test = Livewire::test(ManageFinancialContexts::class)->mountAction('manageContext');

    setContextFormData($test, ['name' => 'Vehículo Turbo', 'is_active' => true])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(FinancialContext::query()->where('name', 'Vehículo Turbo')->where('is_active', true)->exists())->toBeTrue();
});

it('requires a name to create a context', function () {
    $test = Livewire::test(ManageFinancialContexts::class)->mountAction('manageContext');

    setContextFormData($test, ['name' => ''])
        ->callMountedAction()
        ->assertHasFormErrors(['name']);
});

it('updates an existing financial context', function () {
    $context = FinancialContext::factory()->create(['name' => 'Personal']);

    $test = Livewire::test(ManageFinancialContexts::class)
        ->mountAction(TestAction::make('manageContext')->arguments(['context' => $context->id]));

    setContextFormData($test, ['name' => 'Personal y familia'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($context->fresh()->name)->toBe('Personal y familia');
});

it('archives and reactivates a context', function () {
    $context = FinancialContext::factory()->create(['is_active' => true]);

    $test = Livewire::test(ManageFinancialContexts::class)
        ->call('toggleContextActive', $context->id);

    expect($context->fresh()->is_active)->toBeFalse();

    $test->call('toggleContextActive', $context->id);

    expect($context->fresh()->is_active)->toBeTrue();
});

it('deletes a context without movements', function () {
    $context = FinancialContext::factory()->create();

    Livewire::test(ManageFinancialContexts::class)
        ->callAction(TestAction::make('deleteContext')->arguments(['context' => $context->id]));

    expect(FinancialContext::query()->find($context->id))->toBeNull();
});

it('does not delete a context that has movements', function () {
    $context = FinancialContext::factory()->create();
    Movement::factory()->create(['financial_context_id' => $context->id]);

    Livewire::test(ManageFinancialContexts::class)
        ->callAction(TestAction::make('deleteContext')->arguments(['context' => $context->id]));

    expect(FinancialContext::query()->find($context->id))->not->toBeNull();
});

it('creates a category scoped to the selected context', function () {
    $context = FinancialContext::factory()->create();

    $test = Livewire::test(ManageFinancialContexts::class)
        ->call('selectContext', $context->id)
        ->mountAction(TestAction::make('manageCategory')->arguments([
            'financial_context_id' => $context->id,
            'type' => 'expense',
        ]));

    setContextFormData($test, ['name' => 'Combustible'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $category = Category::query()->where('name', 'Combustible')->sole();

    expect($category->financial_context_id)->toBe($context->id);
});

it('creates a category with no context when "General" is selected', function () {
    $test = Livewire::test(ManageFinancialContexts::class)
        ->call('selectContext', null)
        ->mountAction(TestAction::make('manageCategory')->arguments([
            'financial_context_id' => null,
            'type' => 'expense',
        ]));

    setContextFormData($test, ['name' => 'Mercado'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Category::query()->where('name', 'Mercado')->sole()->financial_context_id)->toBeNull();
});

it('only lists categories belonging to the selected context', function () {
    $context = FinancialContext::factory()->create();
    Category::factory()->create(['name' => 'Combustible', 'type' => CategoryType::Expense, 'financial_context_id' => $context->id]);
    Category::factory()->create(['name' => 'Mercado', 'type' => CategoryType::Expense, 'financial_context_id' => null]);

    Livewire::test(ManageFinancialContexts::class)
        ->call('selectContext', $context->id)
        ->assertSee('Combustible')
        ->assertDontSee('Mercado');
});

it('deletes a category', function () {
    $category = Category::factory()->create();

    Livewire::test(ManageFinancialContexts::class)
        ->callAction(TestAction::make('deleteCategory')->arguments(['category' => $category->id]));

    expect(Category::query()->find($category->id))->toBeNull();
});
