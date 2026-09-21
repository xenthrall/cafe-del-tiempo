<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Models\Category;

/**
 * Acción reutilizable para crear o editar una categoría, siempre dentro del
 * contexto financiero activo en la página (o "General", sin contexto — ver
 * ManageFinancialContexts::$selectedContextId). El contexto no es un campo
 * visible del formulario: al crear viene del argumento `financial_context_id`;
 * al editar, del propio registro (`$record->financial_context_id`) — este
 * modal nunca mueve una categoría a otro contexto, solo la crea/edita en el
 * contexto donde ya está el usuario. Va como `Hidden` en el schema (no como
 * argumento leído directamente en `options()`) porque los campos anidados de
 * un schema no reciben `$arguments` de la acción — solo utilidades propias
 * del schema como `Get`/`$record`.
 */
class ManageCategoryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageCategory';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (array $arguments, ?Category $record): string => $this->isEditing($arguments, $record) ? 'Editar categoría' : 'Nueva categoría')
            ->modalHeading(fn (array $arguments, ?Category $record): string => $this->isEditing($arguments, $record) ? 'Editar categoría' : 'Nueva categoría')
            ->icon(fn (array $arguments, ?Category $record) => $this->isEditing($arguments, $record) ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments, ?Category $record): array => $this->fillFormData($arguments, $record))
            ->action(function (array $data, array $arguments, ?Category $record): void {
                $this->save($data, $arguments, $record);
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function isEditing(array $arguments, ?Category $record): bool
    {
        return $this->resolveCategoryId($arguments, $record) !== null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveCategoryId(array $arguments, ?Category $record): ?int
    {
        return $record?->id ?? ($arguments['category'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveContextId(array $arguments, ?Category $record): ?int
    {
        return $record?->financial_context_id ?? ($arguments['financial_context_id'] ?? null);
    }

    /**
     * @return array<int, TextInput|Select|Checkbox|Hidden>
     */
    private function formSchema(): array
    {
        return [
            Hidden::make('financial_context_id'),
            Hidden::make('editing_category_id'),

            TextInput::make('name')
                ->label('Nombre')
                ->placeholder('Transporte, Vivienda…')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Tipo')
                ->options(collect(CategoryType::cases())->mapWithKeys(
                    fn (CategoryType $type): array => [$type->value => $type->label()],
                ))
                ->required()
                ->live()
                ->native(false),

            Select::make('parent_id')
                ->label('Categoría padre (opcional)')
                ->options(fn (Get $get): Collection => $this->parentOptions(
                    $get('type'),
                    $get('financial_context_id'),
                    $get('editing_category_id'),
                    $get('parent_id'),
                ))
                ->native(false),

            Checkbox::make('is_active')
                ->label('Categoría activa (aparece en los selectores al registrar movimientos)')
                ->default(true),
        ];
    }

    private function parentOptions(?string $type, mixed $contextId, mixed $excludeId, mixed $currentParentId): Collection
    {
        if (! in_array($type, [CategoryType::Income->value, CategoryType::Expense->value], true)) {
            return collect();
        }

        return Category::query()
            ->where('type', $type)
            ->where('financial_context_id', $contextId ?: null)
            ->where(fn ($query) => $query
                ->where('is_active', true)
                ->when($currentParentId, fn ($query) => $query->orWhere('id', $currentParentId)))
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function fillFormData(array $arguments, ?Category $record): array
    {
        $categoryId = $this->resolveCategoryId($arguments, $record);

        if ($categoryId === null) {
            return [
                'type' => $arguments['type'] ?? CategoryType::Expense->value,
                'financial_context_id' => $this->resolveContextId($arguments, $record),
                'is_active' => true,
            ];
        }

        $category = $record ?? Category::findOrFail($categoryId);

        return [
            'name' => $category->name,
            'type' => $category->type->value,
            'parent_id' => $category->parent_id,
            'financial_context_id' => $category->financial_context_id,
            'editing_category_id' => $category->id,
            'is_active' => $category->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?Category $record): void
    {
        $data['parent_id'] = $data['parent_id'] ?: null;
        unset($data['editing_category_id']);

        $categoryId = $this->resolveCategoryId($arguments, $record);

        if ($categoryId !== null) {
            ($record ?? Category::findOrFail($categoryId))->update($data);
        } else {
            Category::create($data);
        }
    }
}
