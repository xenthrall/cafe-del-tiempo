<?php

namespace Tequia\Finance\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Finanzas personales: cada registro pertenece a un único usuario. Este
 * trait añade el scope global que filtra automáticamente por el usuario
 * autenticado y asigna `user_id` al crear, para que cada usuario solo vea
 * y gestione lo que le corresponde sin tener que repetirlo en cada consulta.
 */
trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('user', function (Builder $query): void {
            if (auth()->check()) {
                $query->where($query->getModel()->getTable().'.user_id', auth()->id());
            }
        });

        static::creating(function (Model $model): void {
            $model->user_id ??= auth()->id();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
