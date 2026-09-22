<?php

namespace Tequia\Finance\Actions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorContract;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;

/**
 * Valida y prepara los datos de un movimiento según su tipo antes de guardarlo,
 * para que la regla "qué campos aplican a cada tipo" (ver docs/finance.md —
 * Modelo de datos) no dependa de que cada formulario la respete por su cuenta.
 */
class SaveMovement
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): Movement
    {
        return Movement::create($this->prepare($input));
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function update(Movement $movement, array $input): Movement
    {
        $movement->update($this->prepare($input));

        return $movement;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function prepare(array $input): array
    {
        $type = $input['type'] instanceof MovementType ? $input['type'] : MovementType::from($input['type']);

        $validator = Validator::make([...$input, 'type' => $type->value], $this->rules($type));

        if ($type === MovementType::Transfer) {
            $validator->after(fn (ValidatorContract $validator) => $this->validateSameCurrencyTransfer($validator, $input));
        }

        $data = $validator->validate();
        $data['type'] = $type;

        return match ($type) {
            MovementType::Transfer => [...$data, 'account_id' => null, 'category_id' => null],
            MovementType::Adjustment => [...$data, 'from_account_id' => null, 'to_account_id' => null, 'category_id' => null],
            MovementType::Income, MovementType::Expense => [...$data, 'from_account_id' => null, 'to_account_id' => null],
        };
    }

    /**
     * Una transferencia no convierte moneda (ver docs/finance.md —
     * Multi-moneda): origen y destino deben coincidir. La UI ya filtra
     * `to_account_id` a la moneda de `from_account_id` (ver
     * ManageMovementAction), esto es la última línea de defensa en el
     * guardado. Si alguno de los dos IDs ya falló `exists` en `rules()`,
     * `Account::find()` da null y no hay nada que comparar aquí.
     *
     * @param  array<string, mixed>  $input
     */
    private function validateSameCurrencyTransfer(ValidatorContract $validator, array $input): void
    {
        $from = Account::find($input['from_account_id'] ?? null);
        $to = Account::find($input['to_account_id'] ?? null);

        if ($from && $to && $from->currency !== $to->currency) {
            $validator->errors()->add('to_account_id', 'Solo puedes transferir entre cuentas de la misma moneda.');
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(MovementType $type): array
    {
        $userId = auth()->id();

        $shared = [
            'type' => ['required', Rule::enum(MovementType::class)],
            'financial_context_id' => ['nullable', 'integer', Rule::exists('finance_financial_contexts', 'id')->where('user_id', $userId)],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];

        return match ($type) {
            MovementType::Transfer => [
                ...$shared,
                'from_account_id' => ['required', 'integer', 'different:to_account_id', Rule::exists('finance_accounts', 'id')->where('user_id', $userId)],
                'to_account_id' => ['required', 'integer', Rule::exists('finance_accounts', 'id')->where('user_id', $userId)],
                'amount' => ['required', 'numeric', 'gt:0'],
            ],
            MovementType::Adjustment => [
                ...$shared,
                'account_id' => ['required', 'integer', Rule::exists('finance_accounts', 'id')->where('user_id', $userId)],
                'amount' => ['required', 'numeric', 'not_in:0'],
            ],
            MovementType::Income, MovementType::Expense => [
                ...$shared,
                'account_id' => ['required', 'integer', Rule::exists('finance_accounts', 'id')->where('user_id', $userId)],
                'category_id' => ['nullable', 'integer', Rule::exists('finance_categories', 'id')->where('type', $type->value)->where('user_id', $userId)],
                'amount' => ['required', 'numeric', 'gt:0'],
            ],
        };
    }
}
