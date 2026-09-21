# Finanzas — módulo `finance`, documento de planteamiento

Documento vivo, mismo espíritu que [`vision.md`](vision.md): aquí se piensa en voz alta y se dejan preguntas abiertas hasta que algo se cierre de verdad. Cuando algo se cierre, se mueve a "Decidido".

Última actualización: 2026-09-20.

## Estado

El esqueleto del módulo ya existe: `app-modules/finance` (namespace `Tequia\Finance`), creado con `php artisan make:module finance` y registrado en `composer.json` (`tequia/finance`), siguiendo la misma convención modular que `vault`. Todavía no hay modelo de datos, migraciones ni lógica de dominio — solo el andamiaje que genera el paquete (`FinanceServiceProvider`, carpetas `src/`, `database/`, `resources/`, `routes/`, `tests/`).

## Objetivo del módulo

Gestionar y analizar de forma organizada las finanzas personales y actividades económicas del usuario, mediante el registro de ingresos, gastos, transferencias, cuentas y contextos financieros. A largo plazo el módulo debe permitir conocer el flujo de dinero, comparar ingresos frente a gastos e identificar oportunidades de ahorro — pero no todo eso entra en la primera versión (ver fases abajo).

## Fases (decidido)

El alcance se divide explícitamente en fases. Las fases 2 y posteriores no se diseñan todavía — quedan anotadas aquí solo para que el modelo de datos de la fase 1 no las bloquee sin querer.

### Fase 1 — registro y flujo de dinero (alcance actual)

- Registro de **ingresos**, **gastos** y **transferencias**.
- Registro de **cuentas** (dónde vive el dinero).
- Registro de **contextos financieros** (ámbito/actividad al que pertenece un movimiento).
- Registro de **categorías** (definidas por el usuario, jerárquicas, independientes para ingresos y gastos).
- Visibilidad del **flujo de dinero**: de dónde entra, hacia dónde sale.
- **Comparación de ingresos frente a gastos**.

**Oportunidades de ahorro queda fuera de la fase 1** — es un análisis más avanzado sobre el comportamiento de ingresos/gastos y no tiene sentido antes de tener el registro y el flujo de dinero sólidos. Ver "Ideas para fases posteriores".

### Fase 2 — historial y soporte tributario (fuera de alcance por ahora)

- Informes históricos.
- Informes tributarios orientados a facilitar la preparación de la declaración de renta.
- Conservación de los soportes asociados a esos informes.

Nada de esta fase se diseña ni se modela todavía. Se retoma cuando la fase 1 esté sólida.

## Ideas para fases posteriores

Sin comprometerse a una fase ni a un diseño concreto todavía:

- **Oportunidades de ahorro**: análisis del comportamiento de ingresos y gastos para detectar patrones, excesos o posibles reducciones de gasto. No se implementa un sistema de recomendaciones en la primera versión — se retoma cuando el registro base (movimientos, cuentas, categorías) ya esté en uso y haya datos reales sobre los que analizar.
- **Préstamos y deudas con terceros**: una transferencia simple es solo entre cuentas propias (ver "Decidido"); prestarle a alguien o deberle a alguien es un caso distinto. Queda fuera de la fase 1 y sin diseñar todavía — lo único decidido es que el modelo de fase 1 no debe bloquear esta extensión futura (ver "Decidido").
- **Recurrencia programada/proyectada**: el modelo debe quedar preparado desde el diseño para soportar movimientos recurrentes (ver "Decidido"), pero la programación/proyección real (generar automáticamente el siguiente movimiento, proyectar saldo futuro) no se construye en la primera versión.

## Relación con el resto del proyecto

- `finance` sigue la misma convención modular que `vault`: paquete separado en `app-modules/`, `app` solo como capa de panel/UI. Ver [convención modular general](vision.md#decidido).
- El proyecto es **single-user por instancia** y **self-hosted/soberano** a nivel general (ver "Decidido" en `vision.md`) — `finance` hereda esos principios; no se diseñan finanzas compartidas entre usuarios por ahora (ver "Decidido" abajo).
- A diferencia de `vault`, **`finance` no replica el modelo de cifrado zero-knowledge en cliente**: los datos financieros necesitan poder consultarse, agregarse y analizarse en servidor (sumas, comparativos, informes futuros), algo que zero-knowledge impediría por diseño. Para la fase 1 no hay cifrado selectivo de datos financieros — se usan las medidas normales de seguridad de la aplicación y del servidor (ver "Decidido" — Protección de datos).

## Decidido

- **2026-09-20 — Contextos financieros**: representan el ámbito o actividad a la que pertenece un movimiento (ejemplos: `Personal`, `Vehículo Turbo`, `Trabajo`, `Familia`). No son categorías ni centros contables — sirven para separar y analizar actividades económicas distintas dentro de la misma instancia.
- **2026-09-20 — Cuentas**: representan dónde está, se administra o se adeuda el dinero. La primera versión incluye efectivo, cuentas bancarias, billeteras digitales y tarjetas de crédito. Moneda: inicialmente COP, pero el modelo no se bloquea para soportar otras monedas más adelante.
- **2026-09-20 — Transferencias**: movimientos entre cuentas propias; no cuentan como ingreso ni como gasto. Préstamos o deudas con terceros no son una transferencia simple y quedan fuera de la fase 1 (ver más abajo).
- **2026-09-20 — Categorías**: definidas por el usuario y potencialmente jerárquicas (ej. `Transporte > Combustible`, `Vivienda > Servicios`). Existen categorías independientes para ingresos y para gastos.
- **2026-09-20 — Oportunidades de ahorro**: queda fuera de la fase 1, como idea para una fase posterior (ver arriba). No se diseña ni se modela todavía.
- **2026-09-20 — Préstamos y deudas con terceros**: quedan fuera de la fase 1. No se diseñan entidades ni relaciones específicas todavía — el modelo de fase 1 solo debe evitar bloquear esa extensión futura.
- **2026-09-20 — Recurrencia**: el modelo debe poder contemplarla desde el diseño (dejar el punto de extensión claro), pero no es necesario implementar programación/proyección de movimientos recurrentes en la primera versión.
- **2026-09-20 — Multiusuario**: se mantiene el modelo single-user por instancia, igual que el resto del proyecto. No se diseñan finanzas compartidas entre usuarios por ahora.
- **2026-09-20 — Protección de datos (fase 1)**: no se cifran selectivamente los datos financieros en la base de datos. Se usan las medidas normales de seguridad de la aplicación y del servidor, y los datos permanecen consultables y agregables en servidor (sumas, filtros, análisis) — esto es justamente lo que descarta replicar el modelo zero-knowledge de `vault`. Si más adelante aparecen campos especialmente sensibles, se evaluará cifrado a nivel de campo puntual.
- **2026-09-20 — Soportes**: se posponen para la fase 2. No se modelan todavía, salvo que surja una razón técnica clara para dejar desde ya un punto de extensión (p. ej. una columna o tabla vacía preparada, sin lógica encima).
- **2026-09-20 — Saldo**: el saldo de una cuenta debe poder determinarse a partir de sus movimientos, pero también debe existir una forma explícita de establecer el saldo inicial de una cuenta y de registrar ajustes/correcciones — sin falsificar el histórico de movimientos reales para cuadrar el saldo.
- **2026-09-20 — Fechas**: en la primera versión basta con una única fecha de movimiento. El modelo queda abierto para distinguir más adelante fecha de transacción, fecha de contabilización y fecha de pago, si el caso de uso lo exige.
- **2026-09-20 — Caso de uso "Vehículo Turbo"**: se usa como caso de uso real para validar el diseño del módulo, no como motivo para crear ya una entidad `Vehicle`. El contexto financiero `Vehículo Turbo` debe permitir registrar los ingresos que genera y los gastos asociados (combustible, mantenimiento, reparaciones, etc.) usando el modelo genérico de contextos/cuentas/categorías — si en algún punto ese caso de uso exige una entidad propia, se decide entonces.

## Roadmap

Con el objetivo, las fases y los conceptos base de la fase 1 (contextos, cuentas, transferencias, categorías, saldo, fechas, protección de datos) ya decididos, y el esqueleto del módulo (`app-modules/finance`) ya creado, no quedan preguntas abiertas bloqueando el diseño. El siguiente paso es proponer el modelo de datos (migraciones/modelos) para la fase 1.
