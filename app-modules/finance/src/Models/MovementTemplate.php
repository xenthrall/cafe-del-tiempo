<?php

namespace Tequia\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tequia\Finance\Database\Factories\MovementTemplateFactory;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Concerns\BelongsToUser;
use Tequia\Finance\Support\Money;

/**
 * Preset de un movimiento recurrente (arriendo, Netflix, salario…) que el
 * usuario define una vez y luego "usa" para registrar el movimiento real sin
 * volver a teclear todo (ver `ManageMovementAction::fillFormData()`, que
 * precarga el formulario de movimiento desde `toMovementFormData()`). No
 * tiene relación con `Movement` a nivel de base de datos — es solo un molde
 * para el formulario, no historial financiero.
 */
class MovementTemplate extends Model
{
    use BelongsToUser;

    /** @use HasFactory<MovementTemplateFactory> */
    use HasFactory;

    protected $table = 'finance_movement_templates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'account_id',
        'category_id',
        'financial_context_id',
        'amount',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
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
        return Money::format($this->amount, $this->account?->currency ?? 'COP');
    }

    /**
     * Datos listos para precargar el formulario de `ManageMovementAction`
     * (mismas claves que `fillFormData()` espera) — todo salvo `date`, que
     * siempre se pone en la fecha de hoy al usar la plantilla.
     *
     * @return array<string, mixed>
     */
    public function toMovementFormData(): array
    {
        return [
            'type' => $this->type->value,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'financial_context_id' => $this->financial_context_id,
            'amount' => (string) $this->amount,
            'description' => $this->description,
        ];
    }

    protected static function newFactory(): MovementTemplateFactory
    {
        return MovementTemplateFactory::new();
    }
}
