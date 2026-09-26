# Vault — bóveda digital

Bóveda de contraseñas, notas y códigos de recuperación, cifrada en el navegador (zero-knowledge). Cada usuario ve y gestiona solo su propia bóveda.

Última actualización: 2026-09-26.

## Cifrado

Todo el cifrado y descifrado ocurre en el navegador (`resources/js/vault.js`):

- **Derivación de clave:** Argon2id (librería `hash-wasm`) a partir de una **contraseña maestra**, distinta de la contraseña de login. Parámetros por defecto: 19 456 KiB de memoria, 2 iteraciones y paralelismo 1 (recomendación de OWASP, ajustada para WASM).
- **Cifrado:** AES-256-GCM con Web Crypto. Cada blob se guarda como `{ v, iv, ct }` (versión, IV de 12 bytes y texto cifrado con su tag GCM, en base64).
- **Servidor:** solo guarda y transporta blobs cifrados. Nunca ve la contraseña maestra ni el contenido en claro, ni siquiera con acceso root.

Por eso los ítems no usan el CRUD estándar de Filament: `VaultDashboard` recibe y envía payloads que el cliente ya cifró. Cualquier campo que quede en claro en la base de datos es metadata que se filtraría si el servidor se compromete; ese es el criterio para decidir qué se cifra.

## Modelo de datos

Namespace `Tequia\Vault`. Migraciones en `app-modules/vault/database/migrations/`, modelos en `app-modules/vault/src/Models/`.

| Tabla | Columnas propias | Notas |
| --- | --- | --- |
| `vault_folders` | `user_id`, `encrypted_name`, `payload_schema_version` | Agrupa ítems. El nombre va cifrado. |
| `vault_items` | `user_id`, `type` (`VaultItemType`: `password`, `note`, `recovery_code`), `folder_id` (nullable), `is_favorite`, `encrypted_payload`, `payload_schema_version` | Soft delete. `type` e `is_favorite` van en claro; el resto del contenido va en `encrypted_payload`. |
| `vault_item_versions` | `user_id`, `vault_item_id`, `encrypted_payload`, `payload_schema_version` | Snapshot cifrado del contenido **anterior**, creado al editar un ítem. Inmutable (sin `updated_at`). |
| `vault_crypto_settings` | `user_id` (único), `key_salt`, `kdf_memory_cost`, `kdf_iterations`, `kdf_parallelism`, `protocol_version` | Una fila por usuario. No es secreto: es lo que el cliente necesita para volver a derivar la clave. |

Reglas de borrado:
- `vault_items.folder_id` → `nullOnDelete`: borrar una carpeta desvincula sus ítems, no los borra.
- `vault_item_versions.vault_item_id` → `cascadeOnDelete`: borrar un ítem definitivamente borra su historial.
- `user_id` en las cuatro tablas → `restrictOnDelete`: no se puede borrar un usuario con datos en la bóveda.

El contenido de un ítem nunca se modela por columnas: el servidor no puede interpretar el blob, así que no tiene sentido validarlo ni indexarlo por campos.

## Aislamiento por usuario

Los cuatro modelos usan el trait `Tequia\Vault\Models\Concerns\BelongsToUser`:
- **Scope global:** toda consulta Eloquent se filtra por `user_id = auth()->id()`.
- **Asignación automática:** al crear un registro, `user_id` se toma del usuario autenticado si no se pasó.
- **Validación:** las reglas de `VaultDashboard` exigen que `folder_id` e `item_id` pertenezcan al usuario, no solo que existan.
- `VaultCryptoSetting::current()` resuelve la fila del usuario autenticado gracias al scope.

## Interfaz

Una sola página, **`/app/vault-dashboard`** (`VaultDashboard`). Sus acciones de Livewire son:

| Acción | Qué hace |
| --- | --- |
| `createFolder` | Crea una carpeta con el nombre ya cifrado. |
| `createItem` | Crea un ítem con su payload cifrado. |
| `updateItem` | Guarda el contenido anterior en `vault_item_versions` y actualiza el ítem. |
| `toggleFavorite` | Marca o desmarca un ítem como favorito. |
| `deleteItem` | Borrado suave del ítem. |

En el primer acceso, `mount()` crea el salt y los parámetros KDF del usuario si todavía no existen.

## A tener en cuenta

- **Migraciones en producción:** no se editan. Cualquier cambio de esquema va en una migración nueva.
- **Sin recuperación:** si un usuario pierde su contraseña maestra, sus datos son irrecuperables. Es el costo esperado del modelo zero-knowledge; todavía no hay mecanismo de rescate.
- **Respaldos:** el respaldo diario de la base de datos (ver [`docker-deploy.md`](docker-deploy.md#respaldos)) incluye la bóveda, pero solo como blobs cifrados. Restaurarlo no expone el contenido.
- **Fuera de alcance por ahora:** renombrar o borrar carpetas, adjuntos, etiquetas y restaurar versiones anteriores desde la UI.
