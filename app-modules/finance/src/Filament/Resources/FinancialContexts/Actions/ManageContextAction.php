<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Tequia\Finance\Models\FinancialContext;

/**
 * Acción reutilizable para crear o editar un contexto financiero (ver
 * ManageMovementAction para el mismo patrón de doble uso: suelta con
 * `->arguments(['context' => $id])` o con el registro enlazado directo).
 */
class ManageContextAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageContext';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (array $arguments, ?FinancialContext $record): string => $this->isEditing($arguments, $record) ? 'Editar contexto' : 'Nuevo contexto')
            ->modalHeading(fn (array $arguments, ?FinancialContext $record): string => $this->isEditing($arguments, $record) ? 'Editar contexto' : 'Nuevo contexto')
            ->icon(fn (array $arguments, ?FinancialContext $record) => $this->isEditing($arguments, $record) ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments, ?FinancialContext $record): array => $this->fillFormData($arguments, $record))
            ->action(function (array $data, array $arguments, ?FinancialContext $record): void {
                $this->save($data, $arguments, $record);
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function isEditing(array $arguments, ?FinancialContext $record): bool
    {
        return $this->resolveContextId($arguments, $record) !== null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveContextId(array $arguments, ?FinancialContext $record): ?int
    {
        return $record?->id ?? ($arguments['context'] ?? null);
    }

    /**
     * @return array<int, TextInput|Checkbox>
     */
    private function formSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nombre')
                ->placeholder('Personal, Vehículo Turbo, Trabajo…')
                ->required()
                ->maxLength(255),

            Checkbox::make('is_active')
                ->label('Contexto activo (aparece en los selectores al registrar movimientos)')
                ->default(true),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function fillFormData(array $arguments, ?FinancialContext $record): array
    {
        $contextId = $this->resolveContextId($arguments, $record);

        if ($contextId === null) {
            return ['is_active' => true];
        }

        $context = $record ?? FinancialContext::findOrFail($contextId);

        return [
            'name' => $context->name,
            'is_active' => $context->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?FinancialContext $record): void
    {
        $contextId = $this->resolveContextId($arguments, $record);

        if ($contextId !== null) {
            ($record ?? FinancialContext::findOrFail($contextId))->update($data);
        } else {
            FinancialContext::create($data);
        }
    }
}
