# Visión y Roadmap — documento vivo

Este archivo es el espacio de trabajo donde se debate y aterriza la idea del proyecto: qué es, qué no es, y en qué orden se va a construir. A diferencia del README, aquí sí se vale pensar en voz alta, dejar opciones abiertas y contradecirse mientras se decide. Cuando algo se cierre de verdad, se mueve a "Decidido" (o al README si es lo suficientemente estable).

Este documento cubre solo lo transversal al proyecto (posicionamiento, principios que aplican a toda instancia, convenciones compartidas). Todo lo específico de un módulo — qué es, decisiones de diseño, modelo de datos, preguntas abiertas propias — vive en su propio documento dentro de `docs/`. Ver el índice de módulos abajo.

Última actualización: 2026-09-20.

## Módulos

| Módulo | Estado | Documento |
| --- | --- | --- |
| `vault` — bóveda de credenciales | Primer módulo, en construcción | [`docs/vault.md`](vault.md) |
| `finance` — finanzas personales | Fase 1 completa (datos, lógica y UI), lista para probarse | [`docs/finance.md`](finance.md) |

## Posicionamiento del proyecto (decidido)

Café del Tiempo **no es "la bóveda"** — es una plataforma personal auto-alojada, y la bóveda de credenciales (`vault`) es solo el primer módulo construido sobre ella. La idea de fondo es que la arquitectura modular (`internachi/modular`) sirva para alojar varias herramientas útiles para un usuario que quiere registrar y preservar información importante, no una sola. Finanzas personales (`finance`) es el segundo módulo ya en planteamiento, y a futuro podrían sumarse más. Lo que sí se mantiene fijo como identidad del proyecto, independiente del módulo, es que cada instancia es **auto-alojada y soberana**: el usuario tiene su propio sistema, sin depender de terceros por defecto.

## Punto de partida

Café del Tiempo es la idea de una plataforma personal auto-alojada para preservar y gestionar la información que más importa. Es una idea personal en fase muy temprana: hay un esqueleto técnico (Laravel 13 + arquitectura modular vía `internachi/modular` + un módulo `app` con panel Filament v5) y un módulo de dominio en construcción (`vault`), con un segundo módulo (`finance`) todavía en fase de planteamiento sin implementación. Ver el detalle de cada uno en su propio documento (tabla de arriba).

## Cómo está construido hoy (técnico, no producto)

- Laravel 13, PHP 8.3+.
- Modular: cada dominio es un paquete en `app-modules/`.
- `app-modules/app` (`tequia/app`): panel Filament v5 en `/app`, con login, sidebar/topbar propios y dashboard de bienvenida. Es solo andamiaje de UI, sin lógica de negocio.
- Tailwind v4 + Vite, Pest, Pint, SQLite por defecto.

## Ideas en exploración

Espacio libre para anotar ideas sueltas sin comprometerse a nada, a nivel de proyecto (ideas específicas de un módulo van en su propio documento). Vacío por ahora.

## Decidido

- **2026-09-04 — Modelo de usuarios**: single-user. Cada instancia sirve a una sola persona (o unidad familiar de confianza), no es multi-tenant. Aplica a todos los módulos, no solo a `vault`.
- **2026-09-04 — Modelo de despliegue**: 100% self-hosted y soberano. Sin intermediarios ni servicios de terceros obligatorios, sin telemetría ni rastreo de actividad. Corre en servidor propio o máquina local del usuario.
- **2026-09-04 — Convención de módulos**: cada dominio de negocio es un paquete/módulo nuevo y separado de `app`, ya que `app` es exclusivamente la capa de panel/UI (Filament) y no debe contener lógica de dominio. `vault` fue el primero en seguir esta convención (`app-modules/vault`); `finance` la sigue también (`app-modules/finance`, cuando se implemente).
- **2026-09-20 — Posicionamiento del proyecto**: Café del Tiempo se reencuadra como plataforma personal auto-alojada con módulos independientes (`vault` es el primero, no el proyecto entero). Lo fijo como identidad es "auto-alojado y soberano"; el conjunto de módulos puede crecer. Ver [Posicionamiento del proyecto (decidido)](#posicionamiento-del-proyecto-decidido).

## Roadmap

Sin fases ni fechas todavía a nivel de proyecto. `vault` y `finance` están implementados y son multiusuario (cada usuario ve solo sus propios datos) — ver el estado actual de cada uno en su propio documento ([`docs/vault.md`](vault.md), [`docs/finance.md`](finance.md)).
