<?php

namespace Tequia\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tequia\Finance\Database\Factories\MovementFactory;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Concerns\BelongsToUser;
use Tequia\Finance\Support\Money;

class Movement extends Model
{
    use BelongsToUser;

    /** @use HasFactory<MovementFactory> */
    use HasFactory;

    protected $table = 'finance_movements';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'account_id',
        'from_account_id',
        'to_account_id',
        'category_id',
        'financial_context_id',
        'amount',
        'date',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * Cuenta afectada por un ingreso, gasto o ajuste. Vacía en una transferencia,
     * que usa `fromAccount`/`toAccount` en su lugar.
     *
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Cuenta de origen de una transferencia.
     *
     * @return BelongsTo<Account, $this>
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Cuenta de destino de una transferencia.
     *
     * @return BelongsTo<Account, $this>
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<FinancialContext, $this>
     */
    public function financialContext(): BelongsTo
    {
        return $this->belongsTo(FinancialContext::class);
    }

    public function formattedAmount(): string
    {
        return Money::format($this->amount, $this->currency());
    }

    /**
     * Moneda de la cuenta afectada — la de `account` en ingreso/gasto/ajuste,
     * la de `fromAccount` en una transferencia (origen y destino comparten
     * moneda, ver SaveMovement). Se usa también para agrupar totales por
     * moneda en el dashboard y los informes.
     */
    public function currency(): string
    {
        return $this->account?->currency?->value ?? $this->fromAccount?->currency?->value ?? 'COP';
    }

    /**
     * Descripción corta de qué cuenta(s) mueve este movimiento, para listados.
     */
    public function accountsLabel(): string
    {
        return match ($this->type) {
            MovementType::Transfer => sprintf('%s → %s', $this->fromAccount?->name, $this->toAccount?->name),
            default => (string) $this->account?->name,
        };
    }

    protected static function newFactory(): MovementFactory
    {
        return MovementFactory::new();
    }
}
