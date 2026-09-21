<?php

namespace Tequia\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tequia\Finance\Database\Factories\CategoryFactory;
use Tequia\Finance\Enums\CategoryType;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'financial_context_id',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Contexto financiero al que pertenece la categoría (opcional) — para
     * poder ver de un vistazo qué categorías corresponden a qué contexto.
     *
     * @return BelongsTo<FinancialContext, $this>
     */
    public function financialContext(): BelongsTo
    {
        return $this->belongsTo(FinancialContext::class);
    }

    /**
     * Una categoría con movimientos no se puede eliminar sin perder histórico
     * real (`movements.category_id` usa `restrictOnDelete()`) — se archiva
     * en su lugar. Mismo patrón que `Account::hasMovements()`.
     */
    public function hasMovements(): bool
    {
        return $this->movements()->exists();
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
