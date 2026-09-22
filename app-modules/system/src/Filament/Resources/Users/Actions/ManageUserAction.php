<?php

namespace Tequia\System\Filament\Resources\Users\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Acción reutilizable para crear o editar un usuario (mismo patrón dual que
 * `Tequia\Finance\Filament\Resources\Movements\Actions\ManageMovementAction`):
 * sin argumento `user` ni registro enlazado, crea; con `['user' => $id]`
 * (uso suelto) o el registro enlazado directo (`recordAction` de una Table),
 * edita. Toda la validación vive aquí, en el closure de nivel superior
 * `action()` — no en closures anidados del schema, que no reciben
 * `$arguments`/`$record` (ver `.ai/rules/actions.md`).
 */
class ManageUserAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageUser';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (array $arguments, ?User $record): string => $this->isEditing($arguments, $record) ? 'Editar usuario' : 'Nuevo usuario')
            ->modalHeading(fn (array $arguments, ?User $record): string => $this->isEditing($arguments, $record) ? 'Editar usuario' : 'Nuevo usuario')
            ->modalWidth(Width::Medium)
            ->icon(fn (array $arguments, ?User $record): string => $this->isEditing($arguments, $record) ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments, ?User $record): array => $this->fillFormData($arguments, $record))
            ->action(function (array $data, array $arguments, ?User $record, Action $action): void {
                $this->save($data, $arguments, $record, $action);
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function isEditing(array $arguments, ?User $record): bool
    {
        return $this->resolveUserId($arguments, $record) !== null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveUserId(array $arguments, ?User $record): ?int
    {
        return $record?->id ?? ($arguments['user'] ?? null);
    }

    /**
     * @return array<int, Component>
     */
    private function formSchema(): array
    {
        return [
            // Expone si se está editando a los closures anidados de abajo
            // (el helper del password), que no reciben $arguments/$record.
            Hidden::make('id'),

            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Correo electrónico')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->maxLength(255)
                ->helperText(fn (Get $get): ?string => $get('id')
                    ? 'Déjala en blanco para no cambiarla.'
                    : null),

            Toggle::make('is_admin')
                ->label('Administrador del sistema')
                ->helperText('Puede entrar al panel de administración (/system).'),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function fillFormData(array $arguments, ?User $record): array
    {
        $userId = $this->resolveUserId($arguments, $record);

        if ($userId === null) {
            return [
                'is_admin' => false,
            ];
        }

        $user = $record ?? User::findOrFail($userId);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?User $record, Action $action): void
    {
        $userId = $this->resolveUserId($arguments, $record);
        unset($data['id']);

        try {
            $data = validator($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
                'password' => [$userId === null ? 'required' : 'nullable', 'string', 'min:8'],
                'is_admin' => ['boolean'],
            ])->validate();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo guardar el usuario')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            $action->halt();

            return;
        }

        // El cast `hashed` de User se encarga del hash al asignar/guardar.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($userId !== null) {
            ($record ?? User::findOrFail($userId))->update($data);
        } else {
            User::create($data);
        }
    }
}
