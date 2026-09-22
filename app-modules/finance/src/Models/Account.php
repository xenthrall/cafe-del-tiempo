<?php

namespace Tequia\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tequia\Finance\Database\Factories\AccountFactory;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Concerns\BelongsToUser;
use Tequia\Finance\Support\Money;

class Account extends Model
{
    use BelongsToUser;

    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $table = 'finance_accounts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'opening_balance',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Movimientos de ingreso, gasto o ajuste registrados directamente sobre esta cuenta.
     *
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Transferencias salientes, en las que esta cuenta es el origen.
     *
     * @return HasMany<Movement, $this>
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Movement::class, 'from_account_id');
    }

    /**
     * Transferencias entrantes, en las que esta cuenta es el destino.
     *
     * @return HasMany<Movement, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Movement::class, 'to_account_id');
    }

    /**
     * Saldo actual: saldo inicial + ingresos/ajustes positivos - gastos/ajustes negativos
     * +/- transferencias, calculado a partir del histórico de movimientos (no se persiste).
     */
    public function balance(): string
    {
        $balance = bcadd('0', (string) $this->opening_balance, 2);

        foreach ($this->movements as $movement) {
            $balance = match ($movement->type) {
                MovementType::Income, MovementType::Adjustment => bcadd($balance, (string) $movement->amount, 2),
                MovementType::Expense => bcsub($balance, (string) $movement->amount, 2),
                MovementType::Transfer => $balance,
            };
        }

        foreach ($this->outgoingTransfers as $transfer) {
            $balance = bcsub($balance, (string) $transfer->amount, 2);
        }

        foreach ($this->incomingTransfers as $transfer) {
            $balance = bcadd($balance, (string) $transfer->amount, 2);
        }

        return $balance;
    }

    public function formattedBalance(): string
    {
        return Money::format($this->balance(), $this->currency);
    }

    /**
     * Una cuenta con movimientos (propios o de transferencias) no se puede eliminar
     * sin perder histórico real — ver "Saldo" y "Protección de datos" en docs/finance.md.
     */
    public function hasMovements(): bool
    {
        return $this->movements()->exists()
            || $this->outgoingTransfers()->exists()
            || $this->incomingTransfers()->exists();
    }

    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }
}
