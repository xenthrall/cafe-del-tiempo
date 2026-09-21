<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Tequia\Finance\Filament\Resources\FinancialContexts\FinancialContextResource;
use Tequia\Finance\Models\FinancialContext;

class ManageFinancialContexts extends Page
{
    protected static string $resource = FinancialContextResource::class;

    protected string $view = 'finance::filament.resources.financial-contexts.pages.manage-financial-contexts';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $contexts = [];

    public ?int $editingId = null;

    public string $name = '';

    public function mount(): void
    {
        $this->refreshContexts();
    }

    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->dispatch('open-modal', id: 'financial-context-form-modal');
    }

    public function openEditModal(int $contextId): void
    {
        $context = FinancialContext::findOrFail($contextId);

        $this->editingId = $context->id;
        $this->name = $context->name;
        $this->dispatch('open-modal', id: 'financial-context-form-modal');
    }

    public function save(): void
    {
        $data = Validator::make(
            ['name' => $this->name],
            ['name' => ['required', 'string', 'max:255']],
        )->validate();

        if ($this->editingId) {
            FinancialContext::findOrFail($this->editingId)->update($data);
        } else {
            FinancialContext::create($data);
        }

        $this->dispatch('close-modal', id: 'financial-context-form-modal');
        $this->refreshContexts();
    }

    public function delete(int $contextId): void
    {
        FinancialContext::findOrFail($contextId)->delete();

        Notification::make()
            ->title('Contexto eliminado')
            ->success()
            ->send();

        $this->refreshContexts();
    }

    private function refreshContexts(): void
    {
        $this->contexts = FinancialContext::query()
            ->withCount('movements')
            ->orderBy('name')
            ->get()
            ->map(fn (FinancialContext $context): array => [
                'id' => $context->id,
                'name' => $context->name,
                'movementsCount' => $context->movements_count,
            ])
            ->all();
    }
}
