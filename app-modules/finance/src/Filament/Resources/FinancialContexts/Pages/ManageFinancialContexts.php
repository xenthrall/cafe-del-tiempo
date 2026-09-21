<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Pages;

use Filament\Resources\Pages\Page;
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

    public string $categoryType = 'expense';

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

    public function manageContextAction(): ManageContextAction
    {
        return ManageContextAction::make()->after(fn () => $this->refreshContexts());
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
            ->where('type', $this->categoryType)
            ->where('financial_context_id', $this->selectedContextId)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->orderBy('name')])
            ->withCount('movements')
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
            'movementsCount' => $category->movements_count,
            'children' => $category->children
                ->map(fn (Category $child): array => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'movementsCount' => $child->movements()->count(),
                ])
                ->all(),
        ];
    }
}
