<?php

namespace Tequia\Finance\Filament\Traits;

use Illuminate\Support\Collection;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;

/**
 * Consultas de opciones compartidas entre `ManageMovementAction` y
 * `ManageMovementTemplateAction` — ambas construyen un formulario con la
 * misma forma (cuenta, contexto, categoría) y las mismas reglas: solo
 * activos, salvo el valor que el campo ya tenga (para no quitárselo en
 * silencio al editar). Extraído aquí después de que la 2ª copia divergiera
 * sin querer (`->searchable()` se agregó primero solo en una de las dos).
 */
trait BuildsMovementFormOptions
{
    /**
     * `$currency`, si se indica, acota a cuentas de esa moneda (lo usa
     * `ManageMovementAction` para `to_account_id`, que debe coincidir con la
     * moneda de `from_account_id` en una transferencia).
     */
    private function accountOptions(mixed $currentAccountId, ?string $currency = null): Collection
    {
        return Account::query()
            ->when($currency, fn ($query) => $query->where('currency', $currency))
            ->where(fn ($query) => $query
                ->where('is_active', true)
                ->when($currentAccountId, fn ($query) => $query->orWhere('id', $currentAccountId)))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    /**
     * Solo contextos activos — uno archivado no debe ofrecerse en formularios
     * nuevos (ver docs/finance.md — Contextos archivables), salvo que sea
     * justo el que ya tiene el registro que se está editando.
     */
    private function contextOptions(mixed $currentContextId): Collection
    {
        return FinancialContext::query()
            ->where(fn ($query) => $query
                ->where('is_active', true)
                ->when($currentContextId, fn ($query) => $query->orWhere('id', $currentContextId)))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    /**
     * Categorías activas del contexto elegido más las generales (sin
     * contexto), agrupando hijas bajo su padre ("Padre > Hija"). La categoría
     * actual se conserva aunque esté archivada (padre o hija) para no
     * quitársela en silencio al editar.
     *
     * @return Collection<int|string, string>
     */
    private function categoryOptions(?string $type, mixed $contextId, mixed $currentCategoryId): Collection
    {
        if (! in_array($type, [MovementType::Income->value, MovementType::Expense->value], true)) {
            return collect();
        }

        $contextId = $contextId ?: null;

        // Not flatMap(): it collapses via array_merge and discards integer keys,
        // which are the option values a Select needs to stay bound to the id.
        $options = [];

        Category::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->where('financial_context_id', $contextId)
                ->when($contextId, fn ($query) => $query->orWhereNull('financial_context_id')))
            ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->each(function (Category $category) use (&$options): void {
                $options[$category->id] = $category->name;

                foreach ($category->children as $child) {
                    $options[$child->id] = "{$category->name} > {$child->name}";
                }
            });

        if ($currentCategoryId && ! isset($options[$currentCategoryId])) {
            $current = Category::query()->with('parent')->find($currentCategoryId);

            if ($current) {
                $options[$current->id] = $current->parent ? "{$current->parent->name} > {$current->name}" : $current->name;
            }
        }

        return collect($options);
    }
}
