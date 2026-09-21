<?php

namespace Tequia\Finance\Filament\Resources\Accounts\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Models\Account;

/**
 * Acción reutilizable para crear o editar una cuenta (ver ManageMovementAction
 * para el mismo patrón de doble uso: suelta con `->arguments(['account' => $id])`
 * o como `recordAction` de una lista/tabla, con el registro enlazado directo).
 */
class ManageAccountAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageAccount';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (array $arguments, ?Account $record): string => $this->isEditing($arguments, $record) ? 'Editar cuenta' : 'Nueva cuenta')
            ->modalHeading(fn (array $arguments, ?Account $record): string => $this->isEditing($arguments, $record) ? 'Editar cuenta' : 'Nueva cuenta')
            ->icon(fn (array $arguments, ?Account $record) => $this->isEditing($arguments, $record) ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments, ?Account $record): array => $this->fillFormData($arguments, $record))
            ->action(function (array $data, array $arguments, ?Account $record): void {
                $this->save($data, $arguments, $record);
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function isEditing(array $arguments, ?Account $record): bool
    {
        return $this->resolveAccountId($arguments, $record) !== null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveAccountId(array $arguments, ?Account $record): ?int
    {
        return $record?->id ?? ($arguments['account'] ?? null);
    }

    /**
     * @return array<int, TextInput|Select|Checkbox>
     */
    private function formSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nombre')
                ->placeholder('Bancolombia ahorros, Nequi, Efectivo…')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Tipo')
                ->options(collect(AccountType::cases())->mapWithKeys(
                    fn (AccountType $type): array => [$type->value => $type->label()],
                ))
                ->required()
                ->native(false),

            TextInput::make('opening_balance')
                ->label('Saldo inicial (COP)')
                ->numeric()
                ->step(0.01)
                ->default(0)
                ->required()
                ->helperText('Puede ser negativo (por ejemplo, una tarjeta de crédito con saldo pendiente).'),

            Checkbox::make('is_active')
                ->label('Cuenta activa (aparece en los selectores al registrar movimientos)')
                ->default(true),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function fillFormData(array $arguments, ?Account $record): array
    {
        $accountId = $this->resolveAccountId($arguments, $record);

        if ($accountId === null) {
            return [
                'type' => AccountType::Cash->value,
                'opening_balance' => 0,
                'is_active' => true,
            ];
        }

        $account = $record ?? Account::findOrFail($accountId);

        return [
            'name' => $account->name,
            'type' => $account->type->value,
            'opening_balance' => (string) $account->opening_balance,
            'is_active' => $account->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?Account $record): void
    {
        $data['currency'] = 'COP';

        $accountId = $this->resolveAccountId($arguments, $record);

        if ($accountId !== null) {
            ($record ?? Account::findOrFail($accountId))->update($data);
        } else {
            Account::create($data);
        }
    }
}
