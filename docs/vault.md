# Vault — bóveda de credenciales

Bóveda de contraseñas, códigos de recuperación y notas sensibles. Multiusuario: cada usuario ve y gestiona únicamente su propia bóveda.

Última actualización: 2026-09-22.

## Cifrado — zero-knowledge en cliente

Todo el cifrado/descifrado ocurre en el navegador: la clave se deriva de la contraseña maestra con **Argon2id**, y el cifrado es **AES-256-GCM**. El servidor solo almacena y transporta blobs cifrados — nunca ve la contraseña maestra ni el contenido en texto plano, ni siquiera con acceso root al servidor. Por eso los ítems no usan el CRUD estándar de Filament: la vista de la bóveda (`VaultDashboard`) recibe y envía blobs ya cifrados por el cliente.

Cualquier campo que quede en texto plano en la base de datos (ver modelo abajo) es candidato a fuga de metadata si el servidor es comprometido — es el criterio detrás de qué se cifra y qué no.

## Modelo de datos

Namespace `Tequia\Vault`, migraciones en `app-modules/vault/database/migrations/`, modelos en `app-modules/vault/src/Models/`.

| Tabla | Columnas propias | Notas |
| --- | --- | --- |
| `vault_folders` | `user_id`, `encrypted_name` (cifrado), `payload_schema_version` | agrupa ítems |
| `vault_items` | `user_id`, `type` (enum `VaultItemType`: `password`, `note`, `recovery_code`, en plano), `folder_id` (nullable), `is_favorite` (plano), `encrypted_payload` (blob cifrado con el resto del contenido del ítem), `payload_schema_version` | soft delete (`deleted_at`) |
| `vault_item_versions` | `user_id`, `vault_item_id`, `encrypted_payload` (snapshot cifrado), `payload_schema_version` | sin `updated_at` (inmutable una vez escrita); se crea automáticamente al editar un ítem, con el contenido *anterior* |
| `vault_crypto_settings` | `user_id` (único), `key_salt`, `kdf_memory_cost`, `kdf_iterations`, `kdf_parallelism`, `protocol_version` | **una fila por usuario** — no es secreto, es lo que el cliente necesita para volver a derivar la clave del usuario |

Reglas de borrado: `vault_items.folder_id` es `nullOnDelete` (borrar una carpeta no borra sus ítems, solo los desvincula); `vault_item_versions.vault_item_id` es `cascadeOnDelete` (borrar un ítem definitivamente sí borra su historial de versiones). Los cuatro `user_id` usan `restrictOnDelete()` hacia `users`: no se puede borrar un usuario con datos en la bóveda.

Nunca se modela relacionalmente el contenido de un ítem por tipo — el servidor no puede interpretar el blob cifrado, así que no tiene sentido validarlo o indexarlo por campos.

## Aislamiento por usuario

Los 4 modelos usan el trait `Tequia\Vault\Models\Concerns\BelongsToUser` (mismo patrón que `finance`, implementación propia del módulo):
- Scope global: toda consulta Eloquent se filtra por `user_id = auth()->id()` automáticamente.
- `user_id` se asigna solo al crear, desde el usuario autenticado, si no se pasó explícitamente.
- Las reglas de validación de `VaultDashboard` (`folder_id`, `item_id`) exigen que el registro referenciado pertenezca al usuario autenticado, no solo que exista.
- `VaultCryptoSetting::current()` (`static::query()->first()`) resuelve automáticamente a la fila del usuario autenticado gracias al scope — no necesita lógica adicional para ser "por usuario".

## Interfaz (Filament)

Una sola página, **`/app/vault-dashboard`** (`VaultDashboard`): crear/renombrar carpetas, crear/editar/marcar como favorito/eliminar (soft delete) ítems, todo vía Livewire recibiendo el payload ya cifrado del cliente. Al primer acceso se generan (`bootstrap()`) el salt y los parámetros KDF por defecto (Argon2id, tuneados para WASM en navegador) si el usuario todavía no los tiene.

## Cosas a tener en cuenta

- Las migraciones existentes **ya están en producción** — no se editan directamente. Todo cambio de esquema va en una migración nueva (`php artisan make:migration`), aunque sea para ajustar una tabla creada hace poco.
- **No hay protocolo de recuperación**: si un usuario pierde su contraseña maestra sin haber guardado una clave de recuperación aparte, sus datos son irrecuperables por diseño (es el costo esperado de zero-knowledge real). Todavía no existe ningún mecanismo de rescate.
- **No hay estrategia de backup definida** todavía (exportación cifrada, snapshots, etc.) — pendiente.
- Adjuntos, etiquetas con tabla propia, e ícono personalizado de carpeta quedan fuera del alcance actual.
