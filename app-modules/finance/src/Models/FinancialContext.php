<?php

namespace Tequia\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tequia\Finance\Database\Factories\FinancialContextFactory;
use Tequia\Finance\Models\Concerns\BelongsToUser;

class FinancialContext extends Model
{
    use BelongsToUser;

    /** @use HasFactory<FinancialContextFactory> */
    use HasFactory;

    protected $table = 'finance_financial_contexts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Un contexto con movimientos no se puede eliminar sin perder histórico
     * real — mismo protector que `Account::hasMovements()`, ahora respaldado
     * también por `restrictOnDelete()` en `movements.financial_context_id`.
     */
    public function hasMovements(): bool
    {
        return $this->movements()->exists();
    }

    protected static function newFactory(): FinancialContextFactory
    {
        return FinancialContextFactory::new();
    }
}
