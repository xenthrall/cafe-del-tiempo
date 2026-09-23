<?php

namespace Tequia\Finance\Filament\Resources\MovementTemplates\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Filament\Traits\BuildsMovementFormOptions;
use Tequia\Finance\Models\MovementTemplate;

/**
 * Acción reutilizable para crear o editar una plantilla de movimiento
 * frecuente (ver `MovementTemplate`), mismo patrón dual (`arguments`/
 * `$record`) que `ManageAccountAction`/`ManageContextAction`. Solo
 * ingreso/gasto (sin transferencia/ajuste — decisión de alcance), y el monto
 * siempre es fijo (a diferencia del formulario de movimiento, aquí es
 * obligatorio) — se guarda directo, sin `Validator` aparte: no hay ramas
 * condicionales por tipo como en `SaveMovement`, las reglas de los propios
 * campos del formulario ya bastan.
 */
class ManageMovementTemplateAction extends Action
{
    use BuildsMovementFormOptions;

    public static function getDefaultName(): ?string
    {
        return 'manageMovementTemplate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (array $arguments, ?MovementTemplate $record): string => $this->isEditing($arguments, $record) ? 'Editar plantilla' : 'Nueva plantilla')
            ->modalHeading(fn (array $arguments, ?MovementTemplate $record): string => $this->isEditing($arguments, $record) ? 'Editar plantilla' : 'Nueva plantilla')
            ->icon(fn (array $arguments, ?MovementTemplate $record) => $this->isEditing($arguments, $record) ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments, ?MovementTemplate $record): array => $this->fillFormData($arguments, $record))
            ->action(function (array $data, array $arguments, ?MovementTemplate $record): void {
                $this->save($data, $arguments, $record);
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function isEditing(array $arguments, ?MovementTemplate $record): bool
    {
        return $this->resolveTemplateId($arguments, $record) !== null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveTemplateId(array $arguments, ?MovementTemplate $record): ?int
    {
        return $record?->id ?? ($arguments['template'] ?? null);
    }

    /**
     * @return array<int, Component>
     */
    private function formSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nombre')
                ->placeholder('Arriendo, Netflix, Salario…')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Tipo')
                ->options([
                    MovementType::Expense->value => MovementType::Expense->label(),
                    MovementType::Income->value => MovementType::Income->label(),
                ])
                ->required()
                ->live()
                ->native(false),

            Select::make('account_id')
                ->label('Cuenta')
                ->options(fn (Get $get): Collection => $this->accountOptions($get('account_id')))
                ->required()
                ->native(false),

            Select::make('financial_context_id')
                ->label('Contexto financiero (opcional)')
                ->options(fn (Get $get): Collection => $this->contextOptions($get('financial_context_id')))
                ->searchable()
                ->live()
                ->native(false),

            // Buscable: igual que en ManageMovementAction, un usuario con
            // muchas categorías/subcategorías por contexto no debería tener
            // que scrollear la lista para encontrar la suya.
            Select::make('category_id')
                ->label('Categoría (opcional)')
                ->options(fn (Get $get): Collection => $this->categoryOptions($get('type'), $get('financial_context_id'), $get('category_id')))
                ->searchable()
                ->native(false),

            TextInput::make('amount')
                ->label('Monto')
                ->numeric()
                ->step(0.01)
                ->required(),

            Textarea::make('description')
                ->label('Descripción (opcional)')
                ->rows(2),

            Checkbox::make('is_active')
                ->label('Plantilla activa (aparece en Frecuentes)')
                ->default(true),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function fillFormData(array $arguments, ?MovementTemplate $record): array
    {
        $templateId = $this->resolveTemplateId($arguments, $record);

        if ($templateId === null) {
            return [
                'type' => MovementType::Expense->value,
                'is_active' => true,
            ];
        }

        $template = $record ?? MovementTemplate::findOrFail($templateId);

        return [
            'name' => $template->name,
            'type' => $template->type->value,
            'account_id' => $template->account_id,
            'category_id' => $template->category_id,
            'financial_context_id' => $template->financial_context_id,
            'amount' => (string) $template->amount,
            'description' => $template->description,
            'is_active' => $template->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?MovementTemplate $record): void
    {
        $templateId = $this->resolveTemplateId($arguments, $record);

        if ($templateId !== null) {
            ($record ?? MovementTemplate::findOrFail($templateId))->update($data);
        } else {
            MovementTemplate::create($data);
        }
    }
}
