<?php

namespace Tequia\Finance\Filament\Resources\Movements\Pages;

use Filament\Resources\Pages\Page;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Tequia\Finance\Actions\SaveMovement;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Filament\Resources\Movements\MovementResource;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;

class ManageMovements extends Page
{
    protected static string $resource = MovementResource::class;

    protected string $view = 'finance::filament.resources.movements.pages.manage-movements';

    /**
     * Cuántos movimientos recientes se listan. Un vault/finance single-user no
     * necesita paginación real todavía (ver docs/finance.md); si el volumen lo
     * exige más adelante, se reevalúa.
     */
    private const MOVEMENTS_LIMIT = 200;

    public string $activeType = 'all';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $movements = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $accountOptions = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $incomeCategoryOptions = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $expenseCategoryOptions = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $contextOptions = [];

    public ?int $editingId = null;

    public string $type = 'expense';

    /**
     * Los campos de cuenta/categoría/contexto no llevan tipo estricto a propósito:
     * el <select> envía "" cuando no hay selección, y un ?int no admite esa
     * asignación (PHP la rechaza por no ser numérica). Se normalizan a null en save().
     */
    public $accountId = null;

    public $fromAccountId = null;

    public $toAccountId = null;

    public $categoryId = null;

    public $financialContextId = null;

    public string $amount = '';

    public string $date = '';

    public string $description = '';

    public function mount(): void
    {
        $this->refreshOptions();
        $this->refreshMovements();
    }

    public function setActiveType(string $type): void
    {
        $this->activeType = $type;
        $this->refreshMovements();
    }

    public function openCreateModal(?string $type = null): void
    {
        $this->editingId = null;
        $this->type = $type ?? ($this->activeType === 'all' ? MovementType::Expense->value : $this->activeType);
        $this->accountId = null;
        $this->fromAccountId = null;
        $this->toAccountId = null;
        $this->categoryId = null;
        $this->financialContextId = null;
        $this->amount = '';
        $this->date = now()->toDateString();
        $this->description = '';
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'movement-form-modal');
    }

    public function openEditModal(int $movementId): void
    {
        $movement = Movement::findOrFail($movementId);

        $this->editingId = $movement->id;
        $this->type = $movement->type->value;
        $this->accountId = $movement->account_id;
        $this->fromAccountId = $movement->from_account_id;
        $this->toAccountId = $movement->to_account_id;
        $this->categoryId = $movement->category_id;
        $this->financialContextId = $movement->financial_context_id;
        $this->amount = (string) $movement->amount;
        $this->date = $movement->date->toDateString();
        $this->description = (string) $movement->description;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'movement-form-modal');
    }

    public function save(): void
    {
        $input = [
            'type' => $this->type,
            'account_id' => $this->accountId ?: null,
            'from_account_id' => $this->fromAccountId ?: null,
            'to_account_id' => $this->toAccountId ?: null,
            'category_id' => $this->categoryId ?: null,
            'financial_context_id' => $this->financialContextId ?: null,
            'amount' => $this->amount,
            'date' => $this->date,
            'description' => $this->description,
        ];

        try {
            if ($this->editingId) {
                app(SaveMovement::class)->update(Movement::findOrFail($this->editingId), $input);
            } else {
                app(SaveMovement::class)->create($input);
            }
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors());

            return;
        }

        $this->dispatch('close-modal', id: 'movement-form-modal');
        $this->refreshMovements();
    }

    public function delete(int $movementId): void
    {
        Movement::findOrFail($movementId)->delete();

        $this->refreshMovements();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function categoryOptions(): array
    {
        return match ($this->type) {
            CategoryType::Income->value => $this->incomeCategoryOptions,
            CategoryType::Expense->value => $this->expenseCategoryOptions,
            default => [],
        };
    }

    private function refreshOptions(): void
    {
        $this->accountOptions = Account::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Account $account): array => ['id' => $account->id, 'name' => $account->name])
            ->all();

        $this->incomeCategoryOptions = $this->categoryOptionsFor(CategoryType::Income);
        $this->expenseCategoryOptions = $this->categoryOptionsFor(CategoryType::Expense);

        $this->contextOptions = FinancialContext::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (FinancialContext $context): array => ['id' => $context->id, 'name' => $context->name])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function categoryOptionsFor(CategoryType $type): array
    {
        return Category::query()
            ->where('type', $type)
            ->with(['children' => fn ($query) => $query->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->flatMap(function (Category $category): array {
                $options = [['id' => $category->id, 'name' => $category->name]];

                foreach ($category->children as $child) {
                    $options[] = ['id' => $child->id, 'name' => "{$category->name} > {$child->name}"];
                }

                return $options;
            })
            ->all();
    }

    private function refreshMovements(): void
    {
        $this->movements = Movement::query()
            ->with(['account', 'fromAccount', 'toAccount', 'category', 'financialContext'])
            ->when($this->activeType !== 'all', fn ($query) => $query->where('type', $this->activeType))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(self::MOVEMENTS_LIMIT)
            ->get()
            ->map(fn (Movement $movement): array => $this->serializeMovement($movement))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMovement(Movement $movement): array
    {
        return [
            'id' => $movement->id,
            'type' => $movement->type->value,
            'typeLabel' => $movement->type->label(),
            'typeColor' => $movement->type->color(),
            'typeIcon' => $movement->type->icon(),
            'accountsLabel' => $movement->accountsLabel(),
            'categoryName' => $movement->category?->name,
            'contextName' => $movement->financialContext?->name,
            'formattedAmount' => $movement->formattedAmount(),
            'isNegative' => in_array($movement->type, [MovementType::Expense], true)
                || ($movement->type === MovementType::Adjustment && $movement->amount < 0),
            'date' => $movement->date->format('d/m/Y'),
            'description' => $movement->description,
        ];
    }
}
