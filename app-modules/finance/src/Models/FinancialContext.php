<?php

namespace Tequia\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tequia\Finance\Database\Factories\FinancialContextFactory;

class FinancialContext extends Model
{
    /** @use HasFactory<FinancialContextFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    protected static function newFactory(): FinancialContextFactory
    {
        return FinancialContextFactory::new();
    }
}
