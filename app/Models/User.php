<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::deleting(function (User $user): bool {
            if ($user->id === auth()->id()) {
                Notification::make()->danger()->title('No puedes eliminar tu propio usuario.')->send();

                return false;
            }

            if ($user->is_admin && static::query()->where('is_admin', true)->whereKeyNot($user->id)->doesntExist()) {
                Notification::make()->danger()->title('Debe quedar al menos un administrador.')->send();

                return false;
            }

            return true;
        });
    }

    /**
     * El panel `app` (finanzas, bóveda) es de uso personal: cualquier usuario
     * autenticado entra. El panel `system` es administración de la instancia
     * (usuarios, backups, y a futuro roles/permisos) — reservado a `is_admin`,
     * el primer paso hacia un modelo de roles real más adelante.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'system' => (bool) $this->is_admin,
            default => true,
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
