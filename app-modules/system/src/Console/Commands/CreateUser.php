<?php

namespace Tequia\System\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('user:create
    {--name= : Nombre del usuario}
    {--email= : Correo electrónico del usuario}
    {--admin : Da acceso al panel de administración (/system)}')]
#[Description('Crea un usuario de la instancia; con --admin también puede entrar a /system')]
class CreateUser extends Command
{
    /**
     * La contraseña siempre se pide de forma interactiva para que no quede
     * en el historial de la terminal.
     */
    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?? text(label: 'Nombre', required: true),
            'email' => $this->option('email') ?? text(label: 'Correo electrónico', required: true),
            'password' => password(label: 'Contraseña', required: true, hint: 'Mínimo 8 caracteres.'),
            'is_admin' => (bool) $this->option('admin'),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['boolean'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create($validator->validated());

        $this->components->info(sprintf(
            'Usuario %s creado%s.',
            $user->email,
            $user->is_admin ? ' como administrador (acceso a /app y /system)' : ' (acceso a /app)',
        ));

        return self::SUCCESS;
    }
}
