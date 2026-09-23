<?php

namespace Tequia\Finance\Filament\Resources\MovementTemplates\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Tequia\Finance\Filament\Resources\Movements\Actions\ManageMovementAction;
use Tequia\Finance\Filament\Resources\MovementTemplates\Actions\DeleteMovementTemplateAction;
use Tequia\Finance\Filament\Resources\MovementTemplates\Actions\ManageMovementTemplateAction;
use Tequia\Finance\Filament\Resources\MovementTemplates\MovementTemplateResource;
use Tequia\Finance\Filament\Traits\HidesPageHeader;
use Tequia\Finance\Models\MovementTemplate;

/**
 * "Frecuentes": plantillas de movimientos recurrentes (arriendo, Netflix,
 * salario…) que el usuario define una vez y luego usa para registrar el
 * movimiento real en un tap, sin volver a llenar todo el formulario desde
 * cero (ver docs/finance.md). El botón "Registrar" de cada tarjeta reutiliza
 * `ManageMovementAction` — la misma acción de "Nuevo movimiento" de
 * `ManageMovements` — pasándole `arguments(['template' => $id])`, que esa
 * acción usa en su `fillFormData()` para precargar el formulario.
 */
class ManageMovementTemplates extends Page
{
    use HidesPageHeader;

    protected static string $resource = MovementTemplateResource::class;

    protected string $view = 'finance::filament.resources.movement-templates.pages.manage-movement-templates';

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Frecuentes';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $templates = [];

    /**
     * Búsqueda por nombre. `updatedSearch()` la aplica en cuanto cambia (ver
     * `wire:model.live` en la vista) — mismo patrón de filtro "propiedad de
     * página + refresh manual" que `$categoryType` en `ManageFinancialContexts`.
     */
    public string $search = '';

    /**
     * 'all' (por defecto), 'expense' o 'income'.
     */
    public string $typeFilter = 'all';

    /**
     * Si el usuario tiene alguna plantilla, sin importar filtro/búsqueda —
     * para distinguir "todavía no has creado ninguna" de "no hay resultados
     * para este filtro" en el estado vacío (ver la vista).
     */
    public bool $hasAnyTemplates = false;

    public function mount(): void
    {
        $this->refreshTemplates();
    }

    public function updatedSearch(): void
    {
        $this->refreshTemplates();
    }

    public function setTypeFilter(string $type): void
    {
        $this->typeFilter = $type;
        $this->refreshTemplates();
    }

    public function manageMovementTemplateAction(): ManageMovementTemplateAction
    {
        return ManageMovementTemplateAction::make()->after(fn () => $this->refreshTemplates());
    }

    public function deleteMovementTemplateAction(): DeleteMovementTemplateAction
    {
        return DeleteMovementTemplateAction::make()->after(fn () => $this->refreshTemplates());
    }

    public function toggleTemplateActive(int $templateId): void
    {
        $template = MovementTemplate::findOrFail($templateId);
        $template->update(['is_active' => ! $template->is_active]);

        $this->refreshTemplates();
    }

    /**
     * Envuelve `toggleTemplateActive()` como una `Action` para agruparla con
     * `deleteMovementTemplateAction` en un menú `<x-filament-actions::group>`
     * (ver la vista) — mismo patrón mobile-friendly que ya usan
     * `ManageFinancialContexts`/`ManageAccounts`.
     */
    public function toggleTemplateActiveAction(): Action
    {
        return Action::make('toggleTemplateActive')
            ->label(fn (array $arguments): string => ($arguments['active'] ?? true) ? 'Archivar' : 'Reactivar')
            ->icon(fn (array $arguments): string => ($arguments['active'] ?? true) ? 'heroicon-o-archive-box' : 'heroicon-o-archive-box-x-mark')
            ->action(fn (array $arguments) => $this->toggleTemplateActive($arguments['template']));
    }

    /**
     * "Registrar": abre el modal de "Nuevo movimiento" ya precargado con los
     * datos de la plantilla (ver `ManageMovementAction::fillFormData()`).
     */
    public function manageMovementAction(): ManageMovementAction
    {
        return ManageMovementAction::make()->after(
            fn () => Notification::make()->title('Movimiento registrado')->success()->send()
        );
    }

    private function refreshTemplates(): void
    {
        $this->hasAnyTemplates = MovementTemplate::query()->exists();

        $this->templates = MovementTemplate::query()
            ->with(['account', 'category', 'financialContext'])
            ->when($this->typeFilter !== 'all', fn ($query) => $query->where('type', $this->typeFilter))
            ->when(trim($this->search) !== '', fn ($query) => $query->where(
                'name', 'like', '%'.addcslashes(trim($this->search), '%_\\').'%'
            ))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (MovementTemplate $template): array => [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type->value,
                'typeLabel' => $template->type->label(),
                'typeColor' => $template->type->color(),
                'typeIcon' => $template->type->icon(),
                'accountName' => $template->account->name,
                'categoryName' => $template->category?->name,
                'contextName' => $template->financialContext?->name,
                'formattedAmount' => $template->formattedAmount(),
                'isActive' => $template->is_active,
            ])
            ->all();
    }
}
