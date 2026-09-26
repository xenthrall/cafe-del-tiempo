# Finance — finanzas personales

Registro y análisis de finanzas personales: cuentas, ingresos, gastos, transferencias y ajustes, organizados por contextos y categorías, con dashboards y reportes. Cada usuario ve y gestiona solo sus propios datos.

Última actualización: 2026-09-26.

## Privacidad

Este módulo **no es zero-knowledge**. Los datos se guardan en la base de datos sin cifrado a nivel de aplicación, porque el servidor necesita sumarlos, filtrarlos y agregarlos para los dashboards y reportes. La protección es el aislamiento por usuario (ver abajo) y la seguridad normal de la aplicación y del servidor.

## Modelo de datos

Namespace `Tequia\Finance`. Migraciones en `app-modules/finance/database/migrations/`, modelos en `app-modules/finance/src/Models/`.

| Tabla | Columnas propias | Relaciones |
| --- | --- | --- |
| `finance_financial_contexts` | `user_id`, `name`, `is_active` | Tiene muchas categorías y movimientos. |
| `finance_accounts` | `user_id`, `name`, `type` (`AccountType`: `cash`, `bank`, `digital_wallet`, `credit_card`), `currency` (`Currency`: `COP`, `USD`, `EUR`, `MXN`, `ARS`, `BRL`), `is_active` | Tiene muchos movimientos (como `account_id`, `from_account_id` o `to_account_id`). |
| `finance_categories` | `user_id`, `name`, `type` (`CategoryType`: `income`, `expense`), `parent_id`, `financial_context_id` (nullable), `is_active` | Jerarquía de máximo 2 niveles (`Transporte > Combustible`): una subcategoría no puede tener hijas (ver `ManageCategoryAction`). |
| `finance_movements` | `user_id`, `type` (`MovementType`: `income`, `expense`, `transfer`, `adjustment`), `account_id`, `from_account_id` / `to_account_id` (solo `transfer`), `category_id` (solo `income` / `expense`), `financial_context_id`, `amount`, `date`, `description` | Pertenece a cuenta(s), categoría y contexto. |
| `finance_movement_templates` | `user_id`, `name`, `type` (solo `income` / `expense`), `account_id`, `category_id` (nullable), `financial_context_id` (nullable), `amount` (fijo), `description` (nullable), `is_active` | Molde para precargar el formulario de movimiento (`MovementTemplate::toMovementFormData()`). No se relaciona con los movimientos que genera. |

### Saldos

El saldo de una cuenta **no se guarda**: `Account::balance()` lo calcula a partir de sus movimientos con `bcmath`, sin errores de punto flotante. No hay columna de saldo inicial: al crear una cuenta, el saldo inicial se registra como un movimiento `adjustment` (ver `ManageAccountAction`). Así los movimientos son la única fuente de verdad.

### Reglas de borrado

- `user_id` en las cinco tablas → `restrictOnDelete`: no se puede borrar un usuario con datos financieros.
- Movimientos → cuentas, categoría y contexto con `restrictOnDelete`: no se puede borrar una cuenta, categoría o contexto con movimientos.
- `finance_categories.parent_id` y `financial_context_id` → `nullOnDelete`: borrar el padre o el contexto solo desvincula.
- `finance_movement_templates.account_id` → `restrictOnDelete` (ver `Account::hasMovementTemplates()`). Su `category_id` y `financial_context_id` usan `nullOnDelete`, porque una plantilla no es historial.

## Aislamiento por usuario

Los cinco modelos usan el trait `Tequia\Finance\Models\Concerns\BelongsToUser`:
- **Scope global:** toda consulta Eloquent se filtra por `user_id = auth()->id()`. Para ver datos de todos los usuarios habría que usar explícitamente `withoutGlobalScope('user')`.
- **Asignación automática:** al crear un registro, `user_id` se toma del usuario autenticado si no se pasó.
- **Validación:** `SaveMovement::rules()` exige que las cuentas, categorías y contextos referenciados pertenezcan al usuario, no solo que existan.

## Lógica de negocio

- **`Actions\SaveMovement`:** única puerta de entrada para crear o editar movimientos. Valida según el `type`, anula los campos que no aplican (una transferencia nunca lleva categoría) y solo permite transferencias entre cuentas de la misma moneda.
- **Moneda de la cuenta:** se bloquea en el formulario cuando la cuenta ya tiene movimientos, porque cambiarla cambiaría el significado de los montos registrados.
- **Borrado protegido en la UI:** `hasMovements()` en cuentas, categorías y contextos permite avisar antes de intentar borrar. La restricción real está en la base de datos.
- **`Support\Money`:** formatea montos en la moneda de cada cuenta (cualquier código ISO 4217, COP por defecto) con `intl`.
- **`Support\PersonalContextTemplate`:** árbol de categorías de ejemplo para el contexto "Personal".

## Interfaz

Páginas propias, no el CRUD genérico de Filament:

| Ruta | Página | Qué hace |
| --- | --- | --- |
| `/app/finance-dashboard` | `FinanceDashboard` | Saldo total, ingresos, gastos y neto del periodo, tendencia de 6 meses, desglose por contexto o categoría y movimientos recientes editables. |
| `/app/accounts` | `ManageAccounts` | Tarjetas de cuentas con saldo en vivo: crear, editar, archivar y eliminar. |
| `/app/movements` | `ManageMovements` | Tabla con vista de tarjetas o columnas, filtros por tipo, periodo, contexto y categoría, y reporte en Excel (OpenSpout) o PDF (`barryvdh/laravel-dompdf`) de lo filtrado. |
| `/app/financial-contexts` | `ManageFinancialContexts` | Contextos y sus categorías en una vista maestro-detalle. Si el usuario no tiene contextos, puede crear el "Personal" de ejemplo (`CreateSampleContextAction`). |
| `/app/movement-templates` | `ManageMovementTemplates` ("Frecuentes") | Plantillas de movimientos recurrentes (arriendo, salario…). "Registrar" abre el formulario de movimiento precargado. |

## A tener en cuenta

- **Migraciones en producción:** no se editan. Cualquier cambio de esquema va en una migración nueva.
- **Sin conversión de monedas:** cada cuenta tiene la suya y no hay tipos de cambio.
- **Fuera de alcance por ahora:** préstamos y deudas con terceros, movimientos recurrentes automáticos e informes tributarios.
