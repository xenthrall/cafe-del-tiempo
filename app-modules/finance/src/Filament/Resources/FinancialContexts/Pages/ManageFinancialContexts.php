<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Tequia\Finance\Filament\Resources\FinancialContexts\Actions\CreateSampleContextAction;
use Tequia\Finance\Filament\Resources\FinancialContexts\Actions\DeleteCategoryAction;
use Tequia\Finance\Filament\Resources\FinancialContexts\Actions\DeleteContextAction;
use Tequia\Finance\Filament\Resources\FinancialContexts\Actions\ManageCategoryAction;
use Tequia\Finance\Filament\Resources\FinancialContexts\Actions\ManageContextAction;
use Tequia\Finance\Filament\Resources\FinancialContexts\FinancialContextResource;
use Tequia\Finance\Filament\Traits\HidesPageHeader;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;

/**
 * Gestión combinada de contextos financieros y sus categorías, estilo SPA:
 * panel maestro de contextos a la izquierda (o arriba, en móvil), panel de
 * categorías del contexto seleccionado a la derecha — sin salir de la
 * página. Reemplaza al antiguo `CategoryResource`/`ManageCategories`
 * (ver docs/finance.md), ahora que `categories.financial_context_id` existe.
 */
class ManageFinancialContexts extends Page
{
    use HidesPageHeader;

    protected static string $resource = FinancialContextResource::class;

    protected string $view = 'finance::filament.resources.financial-contexts.pages.manage-financial-contexts';

    /**
     * El encabezado de página está oculto (ver HidesPageHeader); esto solo
     * queda como título de la pestaña del navegador.
     */
    protected static ?string $title = 'Contextos';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $contexts = [];

    /**
     * Contexto seleccionado en el panel de categorías. `null` es una
     * selección válida: representa "General", las categorías sin contexto.
     */
    public ?int $selectedContextId = null;

    /**
     * 'all' (por defecto), 'expense' o 'income'.
     */
    public string $categoryType = 'all';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $categories = [];

    public function mount(): void
    {
        $this->refreshContexts();
        $this->refreshCategories();
    }

    public function selectContext(?int $contextId): void
    {
        $this->selectedContextId = $contextId;
        $this->refreshCategories();
    }

    public function toggleContextActive(int $contextId): void
    {
        $context = FinancialContext::findOrFail($contextId);
        $context->update(['is_active' => ! $context->is_active]);

        $this->refreshContexts();
    }

    public function setCategoryType(string $type): void
    {
        $this->categoryType = $type;
        $this->refreshCategories();
    }

    public function toggleCategoryActive(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => ! $category->is_active]);

        $this->refreshCategories();
    }

    public function manageContextAction(): ManageContextAction
    {
        return ManageContextAction::make()->after(fn () => $this->refreshContexts());
    }

    public function createSampleContextAction(): CreateSampleContextAction
    {
        return CreateSampleContextAction::make()->after(function (): void {
            $this->refreshContexts();

            $this->selectedContextId = collect($this->contexts)->firstWhere('name', 'Personal')['id'] ?? null;

            $this->refreshCategories();
        });
    }

    public function deleteContextAction(): DeleteContextAction
    {
        return DeleteContextAction::make()->after(function (): void {
            $this->refreshContexts();

            if (! collect($this->contexts)->contains('id', $this->selectedContextId)) {
                $this->selectedContextId = null;
            }

            $this->refreshCategories();
        });
    }

    public function manageCategoryAction(): ManageCategoryAction
    {
        return ManageCategoryAction::make()->after(fn () => $this->refreshCategories());
    }

    public function deleteCategoryAction(): DeleteCategoryAction
    {
        return DeleteCategoryAction::make()->after(fn () => $this->refreshCategories());
    }

    /**
     * Envuelve `toggleContextActive()` como una `Action` para poder agruparla
     * con `deleteContextAction` en un menú `<x-filament-actions::group>` (ver
     * la vista) — en móvil, tres iconButton sueltos y pegados son difíciles
     * de tocar sin errar, así que solo "editar" queda como botón grande y
     * suelto; archivar/eliminar van al menú "más acciones".
     */
    public function toggleContextActiveAction(): Action
    {
        return Action::make('toggleContextActive')
            ->label(fn (array $arguments): string => $this->toggleActiveLabel($arguments))
            ->icon(fn (array $arguments): string => $this->toggleActiveIcon($arguments))
            ->action(fn (array $arguments) => $this->toggleContextActive($arguments['context']));
    }

    /**
     * Mismo envoltorio que `toggleContextActiveAction()` mas para
     * `toggleCategoryActive()` (categorías padre e hijas).
     */
    public function toggleCategoryActiveAction(): Action
    {
        return Action::make('toggleCategoryActive')
            ->label(fn (array $arguments): string => $this->toggleActiveLabel($arguments))
            ->icon(fn (array $arguments): string => $this->toggleActiveIcon($arguments))
            ->action(fn (array $arguments) => $this->toggleCategoryActive($arguments['category']));
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function toggleActiveLabel(array $arguments): string
    {
        return ($arguments['active'] ?? true) ? 'Archivar' : 'Reactivar';
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function toggleActiveIcon(array $arguments): string
    {
        return ($arguments['active'] ?? true) ? 'heroicon-o-archive-box' : 'heroicon-o-archive-box-x-mark';
    }

    private function refreshContexts(): void
    {
        $this->contexts = FinancialContext::query()
            ->withCount(['movements', 'categories'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (FinancialContext $context): array => [
                'id' => $context->id,
                'name' => $context->name,
                'isActive' => $context->is_active,
                'movementsCount' => $context->movements_count,
                'categoriesCount' => $context->categories_count,
            ])
            ->all();
    }

    private function refreshCategories(): void
    {
        $this->categories = Category::query()
            ->when($this->categoryType !== 'all', fn ($query) => $query->where('type', $this->categoryType))
            ->where('financial_context_id', $this->selectedContextId)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->orderByDesc('is_active')->orderBy('name')])
            ->withCount('movements')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => $this->serializeCategory($category))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'type' => $category->type->value,
            'typeLabel' => $category->type->label(),
            'movementsCount' => $category->movements_count,
            'isActive' => $category->is_active,
            'children' => $category->children
                ->map(fn (Category $child): array => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'type' => $child->type->value,
                    'typeLabel' => $child->type->label(),
                    'movementsCount' => $child->movements()->count(),
                    'isActive' => $child->is_active,
                ])
                ->all(),
        ];
    }
}
