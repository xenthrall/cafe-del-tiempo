<?php

use App\Models\User;
use Livewire\Livewire;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Filament\Resources\Categories\Pages\ManageCategories;
use Tequia\Finance\Models\Category;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    $this->get(ManageCategories::getUrl())->assertSuccessful();
});

it('creates a top-level expense category', function () {
    Livewire::test(ManageCategories::class)
        ->set('name', 'Transporte')
        ->set('type', 'expense')
        ->call('save')
        ->assertHasNoErrors();

    expect(Category::query()->where('name', 'Transporte')->where('type', CategoryType::Expense)->exists())->toBeTrue();
});

it('creates a child category under a parent of the same type', function () {
    $parent = Category::factory()->create(['name' => 'Transporte', 'type' => CategoryType::Expense]);

    Livewire::test(ManageCategories::class)
        ->set('name', 'Combustible')
        ->set('type', 'expense')
        ->set('parentId', $parent->id)
        ->call('save')
        ->assertHasNoErrors();

    $child = Category::query()->where('name', 'Combustible')->sole();

    expect($child->parent_id)->toBe($parent->id);
});

it('creates a category when the parent select is left empty', function () {
    // The "no parent" <option> sends an empty string, exactly like an
    // unselected <select> would over the wire — not a PHP null.
    Livewire::test(ManageCategories::class)
        ->set('name', 'Vivienda')
        ->set('type', 'expense')
        ->set('parentId', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(Category::query()->where('name', 'Vivienda')->sole()->parent_id)->toBeNull();
});

it('rejects a parent category of a different type', function () {
    $incomeParent = Category::factory()->create(['type' => CategoryType::Income]);

    Livewire::test(ManageCategories::class)
        ->set('name', 'Combustible')
        ->set('type', 'expense')
        ->set('parentId', $incomeParent->id)
        ->call('save')
        ->assertHasErrors(['parent_id']);
});

it('switches the active tab between expense and income categories', function () {
    Category::factory()->create(['name' => 'Salario', 'type' => CategoryType::Income]);
    Category::factory()->create(['name' => 'Transporte', 'type' => CategoryType::Expense]);

    Livewire::test(ManageCategories::class)
        ->call('setActiveType', 'income')
        ->assertSet('activeType', 'income')
        ->assertSee('Salario')
        ->assertDontSee('Transporte');
});

it('deletes a category', function () {
    $category = Category::factory()->create();

    Livewire::test(ManageCategories::class)->call('delete', $category->id);

    expect(Category::query()->find($category->id))->toBeNull();
});
