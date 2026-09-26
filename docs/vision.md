# Visión

Qué es Café del Tiempo, qué principios lo guían y qué está decidido. Lo específico de cada módulo vive en su propio documento.

Última actualización: 2026-09-26.

## Qué es

Una suite personal de código abierto (MIT) para proteger y organizar información importante, construida como módulos independientes. Se usa de dos formas: en la versión alojada gratuita ([cafe.tequia.dev](https://cafe.tequia.dev)) o instalando una instancia propia.

## Módulos

| Módulo | Estado | Documento |
| --- | --- | --- |
| `vault` — bóveda digital | En producción | [`vault.md`](vault.md) |
| `finance` — finanzas personales | En producción | [`finance.md`](finance.md) |

Además hay dos módulos de infraestructura sin lógica de dominio: `app` (panel personal en `/app`, login, registro y dashboard) y `system` (panel de administración en `/system` y el comando `user:create`).

## Principios

- **Privacidad honesta por módulo.** Cada módulo promete solo lo que cumple. La bóveda es zero-knowledge; finanzas no, porque el servidor necesita leer los datos para calcular dashboards y reportes.
- **Mismo código en todas partes.** La versión alojada y las instancias propias corren el mismo código, sin funciones exclusivas.
- **Módulos independientes.** Cada dominio es un paquete en `app-modules/` (`internachi/modular`). `app` solo es la capa de UI.
- **Sin dependencias obligatorias de terceros.** Una instancia funciona sin servicios externos; R2 para respaldos es opcional.
- **Módulos de bajo costo.** Se priorizan módulos cuyo costo no crece mucho con cada usuario, para que la versión gratuita sea sostenible.

## Modelo de usuarios e instancias

- **Multiusuario con aislamiento por `user_id`.** Cada usuario ve solo sus datos, mediante un scope global en cada modelo de dominio.
- **Dos modos de instancia** (`APP_INSTANCE`):
  - `self-hosted` (por defecto): registro cerrado, las cuentas se crean con `php artisan user:create` o desde `/system`.
  - `hosted`: registro público en `/app/register`. Es el modo de cafe.tequia.dev.
- **Administradores:** `is_admin` da acceso a `/system`. No hay roles más finos todavía.

## Decisiones

| Fecha | Decisión |
| --- | --- |
| 2026-09-04 | Cada dominio es un módulo separado de `app`, que no contiene lógica de negocio. |
| 2026-09-20 | El proyecto es una plataforma de módulos, no "la bóveda". |
| 2026-09-21 | Multiusuario con aislamiento por `user_id`, en lugar de una instancia por persona. |
| 2026-09-26 | Existen dos modos de instancia (`hosted` / `self-hosted`) y la versión alojada es gratuita. |
| 2026-09-26 | La landing solo se indexa en cafe.tequia.dev; las demás instancias llevan `noindex`. |

## Pendientes

- Tope de usuarios registrados en la versión alojada (`APP_MAX_USERS`), para controlar el costo.
- Verificación de email y límite de intentos en el registro público.
- Mecanismo de recuperación de la bóveda si se pierde la contraseña maestra.
- Documentación de arquitectura en la landing (`/docs`).
