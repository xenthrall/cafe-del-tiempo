# Finanzas — módulo `finance`

Registro y análisis de finanzas personales: ingresos, gastos, transferencias, cuentas y contextos financieros. Multiusuario: cada usuario ve y gestiona únicamente sus propios datos.

Última actualización: 2026-09-22.

## Modelo de datos

Namespace `Tequia\Finance`, migraciones en `app-modules/finance/database/migrations/`, modelos en `app-modules/finance/src/Models/`.

| Tabla | Columnas propias | Relaciones |
| --- | --- | --- |
| `finance_financial_contexts` | `user_id`, `name`, `is_active` | tiene muchas `finance_categories`, `finance_movements` |
| `finance_accounts` | `user_id`, `name`, `type` (enum `AccountType`: `cash`, `bank`, `digital_wallet`, `credit_card`), `currency` (enum `Currency`: `COP`, `USD`, `EUR`, `MXN`, `ARS`, `BRL`), `is_active` | tiene muchos `finance_movements` (como `account_id`, `from_account_id`, `to_account_id`) |
| `finance_categories` | `user_id`, `name`, `type` (enum `CategoryType`: `income`, `expense`), `parent_id` (auto-referencia, para jerarquía tipo `Transporte > Combustible`), `financial_context_id` (nullable), `is_active` | pertenece a un `parent`, a un `financialContext` |
| `finance_movements` | `user_id`, `type` (enum `MovementType`: `income`, `expense`, `transfer`, `adjustment`), `account_id`, `from_account_id`/`to_account_id` (solo `transfer`), `category_id` (solo `income`/`expense`), `financial_context_id`, `amount`, `date`, `description` | pertenece a `account`/`fromAccount`/`toAccount`, `category`, `financialContext` |

Todas las tablas están en texto plano — no hay cifrado selectivo de datos financieros (se necesitan sumar, filtrar y analizar en servidor). Se usan las medidas normales de seguridad de la app.

**`user_id` es obligatorio en las cuatro tablas** y usa `restrictOnDelete()` hacia `users`: no se puede borrar un usuario que tenga cualquier dato financiero registrado.

Otras reglas de borrado (`restrictOnDelete` salvo que se indique lo contrario):
- `finance_movements.account_id`/`from_account_id`/`to_account_id` → no se puede borrar una cuenta con movimientos.
- `finance_movements.category_id`, `finance_movements.financial_context_id` → no se puede borrar una categoría/contexto con movimientos.
- `finance_categories.parent_id`, `finance_categories.financial_context_id` → `nullOnDelete`: borrar el padre/contexto solo desvincula, no borra la categoría.

El saldo de una cuenta **no se persiste**: `Account::balance()` lo calcula sumando sus movimientos (con `bcmath`, sin errores de punto flotante). No existe una columna de saldo inicial — al crear una cuenta, lo que el usuario indique como saldo inicial se registra como un movimiento de tipo `adjustment` más (ver `ManageAccountAction::save()`), para que los movimientos sean la única fuente de verdad y nunca puedan desincronizarse de un campo aparte.

## Aislamiento por usuario

Los 4 modelos usan el trait `Tequia\Finance\Models\Concerns\BelongsToUser`:
- Agrega un **scope global** que filtra toda consulta Eloquent por `user_id = auth()->id()` — no hace falta acordarse de filtrar manualmente en ningún resource, página o acción.
- Al crear un registro, asigna `user_id` automáticamente desde el usuario autenticado si no se indicó explícitamente.
- Las reglas de validación (`SaveMovement::rules()`) también exigen que las cuentas/categorías/contextos referenciados (`account_id`, `category_id`, etc.) pertenezcan al usuario autenticado — no solo que existan.

Consecuencia práctica: cualquier `Account::query()`, `Movement::query()`, etc. en el código ya está scopeado; una consulta que necesite ver todos los usuarios tendría que usar explícitamente `withoutGlobalScope('user')`.

## Lógica de negocio

- **`Tequia\Finance\Actions\SaveMovement`**: única puerta de entrada para crear/editar un movimiento. Valida según `type` y anula los campos que no aplican (p. ej. una transferencia nunca lleva `category_id`).
- **Borrado protegido en UI**: `Account::hasMovements()` / `FinancialContext::hasMovements()` / `Category::hasMovements()` permiten avisar antes de intentar borrar (el respaldo real es la restricción de base de datos de arriba).
- **`Tequia\Finance\Support\Money`**: formatea montos en pesos colombianos (`$ 1.234.567,89`).

## Interfaz (Filament)

Páginas custom (no CRUD genérico de Filament):

- **`/app/accounts`** (`ManageAccounts`): tarjetas de cuentas con saldo en vivo, crear/editar/archivar/eliminar.
- **`/app/movements`** (`ManageMovements`): listado con tabla real de Filament (paginación, dos vistas: tarjetas/columnas), filtros por tipo/periodo/contexto/categoría, exportación a Excel o PDF del listado filtrado.
- **`/app/financial-contexts`** (`ManageFinancialContexts`): gestión combinada de contextos y sus categorías en una sola pantalla tipo maestro-detalle.
- **`/app/finance-dashboard`** (`FinanceDashboard`): saldo total, ingresos/gastos/neto del periodo filtrado, tendencia de 6 meses, desglose de gastos/ingresos por contexto o categoría, movimientos recientes editables desde ahí mismo.

## Cosas a tener en cuenta

- Las migraciones se editan **directamente** cuando cambia el esquema (no se van acumulando migraciones nuevas por cada ajuste) — si tu base de datos ya estaba migrada con una versión anterior del esquema, necesitas `migrate:fresh` (o equivalente) para que quede al día. Tú te encargas de correrlo.
- No hay conversión automática entre monedas: cada cuenta tiene la suya, y una transferencia solo se permite entre cuentas de la misma moneda.
- No hay soporte para préstamos/deudas con terceros, ni recurrencia programada, ni informes tributarios — quedan fuera del alcance actual.
