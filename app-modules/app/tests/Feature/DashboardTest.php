<?php

use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tequia\App\Filament\Pages\Dashboard;
use Tequia\App\Filament\Widgets\WelcomeWidget;

beforeEach(function () {
    Filament::setCurrentPanel('app');
    $this->actingAs(User::factory()->create(['name' => 'Ana María López']));
});

it('greets the user by first name', function () {
    Livewire::test(WelcomeWidget::class)
        ->assertSeeText('Ana')
        ->assertDontSeeText('María');
});

it('links to every module and its sections', function () {
    $this->get(Dashboard::getUrl())
        ->assertSuccessful()
        ->assertSee(route('filament.app.pages.vault-dashboard'))
        ->assertSee(route('filament.app.pages.finance-dashboard'))
        ->assertSee(route('filament.app.resources.movements.index'))
        ->assertSee(route('filament.app.resources.accounts.index'))
        ->assertSee(route('filament.app.resources.movement-templates.index'))
        ->assertSee(route('filament.app.resources.financial-contexts.index'));
});

it('no longer shows the AI assistant preview', function () {
    $this->get(Dashboard::getUrl())
        ->assertSuccessful()
        ->assertDontSee('Café IA');
});
