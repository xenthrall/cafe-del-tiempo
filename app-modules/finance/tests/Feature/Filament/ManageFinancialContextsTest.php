<?php

use App\Models\User;
use Livewire\Livewire;
use Tequia\Finance\Filament\Resources\FinancialContexts\Pages\ManageFinancialContexts;
use Tequia\Finance\Models\FinancialContext;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    $this->get(ManageFinancialContexts::getUrl())->assertSuccessful();
});

it('creates a financial context', function () {
    Livewire::test(ManageFinancialContexts::class)
        ->set('name', 'Vehículo Turbo')
        ->call('save')
        ->assertHasNoErrors();

    expect(FinancialContext::query()->where('name', 'Vehículo Turbo')->exists())->toBeTrue();
});

it('requires a name to create a context', function () {
    Livewire::test(ManageFinancialContexts::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});

it('updates an existing financial context', function () {
    $context = FinancialContext::factory()->create(['name' => 'Personal']);

    Livewire::test(ManageFinancialContexts::class)
        ->call('openEditModal', $context->id)
        ->set('name', 'Personal y familia')
        ->call('save')
        ->assertHasNoErrors();

    expect($context->fresh()->name)->toBe('Personal y familia');
});

it('deletes a financial context', function () {
    $context = FinancialContext::factory()->create();

    Livewire::test(ManageFinancialContexts::class)->call('delete', $context->id);

    expect(FinancialContext::query()->find($context->id))->toBeNull();
});
