<?php

namespace Tequia\Finance\Filament\Resources\Accounts\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\DB;
use Tequia\Finance\Actions\SaveMovement;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Enums\Currency;
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
     * @return array<int, TextInput|Select|Checkbox|Hidden>
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

            // Deshabilitado (no oculto) para una cuenta con movimientos: el
            // usuario debe seguir viendo en qué moneda quedó la cuenta.
            // Cambiarla resignificaría en silencio los montos ya registrados
            // (ver Account::hasMovements(), mismo criterio que protege el
            // borrado en docs/finance.md). currency_locked viaja como campo
            // oculto porque un closure de un campo anidado del schema no
            // recibe $record (ver .ai/rules/actions.md).
            Select::make('currency')
                ->label('Moneda')
                ->options(collect(Currency::cases())->mapWithKeys(
                    fn (Currency $currency): array => [$currency->value => $currency->label()],
                ))
                ->required()
                ->native(false)
                ->disabled(fn (Get $get): bool => (bool) $get('currency_locked'))
                ->helperText(fn (Get $get): ?string => $get('currency_locked')
                    ? 'Esta cuenta ya tiene movimientos, así que su moneda no se puede cambiar.'
                    : null),

            Hidden::make('currency_locked'),

            Hidden::make('is_editing'),

            // El saldo de una cuenta no se persiste como campo propio — los
            // movimientos son la única fuente de verdad (ver Account::balance()).
            // Por eso este campo solo existe al crear: lo que el usuario
            // escriba aquí se guarda como un movimiento de ajuste inicial
            // (ver save()), no como una columna que pudiera desincronizarse
            // si luego se corrige. Al editar no tiene sentido: para corregir
            // el saldo de una cuenta existente se registra un ajuste nuevo
            // desde Movimientos, igual que cualquier otra corrección.
            TextInput::make('opening_balance')
                ->label('Saldo inicial')
                ->numeric()
                ->step(0.01)
                ->default(0)
                ->visible(fn (Get $get): bool => ! $get('is_editing'))
                ->required(fn (Get $get): bool => ! $get('is_editing'))
                ->helperText('Puede ser negativo (por ejemplo, una tarjeta de crédito con saldo pendiente). Se registra como un movimiento de ajuste inicial.'),

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
                'currency' => Currency::Cop->value,
                'currency_locked' => false,
                'is_editing' => false,
                'opening_balance' => 0,
                'is_active' => true,
            ];
        }

        $account = $record ?? Account::findOrFail($accountId);

        return [
            'name' => $account->name,
            'type' => $account->type->value,
            'currency' => $account->currency->value,
            'currency_locked' => $account->hasMovements(),
            'is_editing' => true,
            'is_active' => $account->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?Account $record): void
    {
        unset($data['currency_locked'], $data['is_editing']);

        $openingBalance = $data['opening_balance'] ?? null;
        unset($data['opening_balance']);

        $accountId = $this->resolveAccountId($arguments, $record);

        DB::transaction(function () use ($data, $accountId, $record, $openingBalance): void {
            if ($accountId !== null) {
                ($record ?? Account::findOrFail($accountId))->update($data);

                return;
            }

            $account = Account::create($data);

            // Solo al crear (ver el campo en formSchema()): el saldo inicial
            // se registra como un movimiento de ajuste, no como una columna
            // de Account — los movimientos son la única fuente de verdad
            // del saldo (ver Account::balance()).
            if ($openingBalance !== null && bccomp((string) $openingBalance, '0', 2) !== 0) {
                app(SaveMovement::class)->create([
                    'type' => 'adjustment',
                    'account_id' => $account->id,
                    'amount' => $openingBalance,
                    'date' => now()->toDateString(),
                    'description' => 'Saldo inicial',
                ]);
            }
        });
    }
}
