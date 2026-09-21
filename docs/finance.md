# Finanzas — módulo `finance`, documento de planteamiento

Documento vivo, mismo espíritu que [`vision.md`](vision.md): aquí se piensa en voz alta y se dejan preguntas abiertas hasta que algo se cierre de verdad. Es el punto de partida para diseñar el módulo `finance`, todavía no hay modelo de datos ni implementación.

Última actualización: 2026-09-20.

## Objetivo del módulo

Gestionar y analizar de forma organizada las finanzas personales y actividades económicas del usuario, mediante el registro de ingresos, gastos, transferencias, cuentas y contextos financieros. El módulo debe permitir conocer el flujo de dinero, comparar ingresos frente a gastos e identificar oportunidades de ahorro.

## Fases (decidido)

El alcance se divide explícitamente en dos fases. **La fase 2 no se diseña todavía** — queda anotada aquí solo para que el modelo de datos de la fase 1 no la bloquee sin querer (por ejemplo, si algo de conservación de soportes debiera empezar a capturarse desde ya, se decide más adelante, no ahora).

### Fase 1 — registro y flujo de dinero (alcance actual)

- Registro de **ingresos**, **gastos** y **transferencias**.
- Registro de **cuentas** (dónde vive el dinero).
- Registro de **contextos financieros** (agrupación/clasificación de la actividad económica — definición exacta pendiente, ver preguntas abiertas).
- Visibilidad del **flujo de dinero**: de dónde entra, hacia dónde sale.
- **Comparación de ingresos frente a gastos**.
- **Identificación de oportunidades de ahorro**.

### Fase 2 — historial y soporte tributario (fuera de alcance por ahora)

- Informes históricos.
- Informes tributarios orientados a facilitar la preparación de la declaración de renta.
- Conservación de los soportes asociados a esos informes.

Nada de esta fase se diseña ni se modela todavía. Se retoma cuando la fase 1 esté sólida.

## Relación con el resto del proyecto

- Café del Tiempo ya tiene un módulo (`app-modules/vault`) construido bajo la convención modular del proyecto (`internachi/modular`) — `app` es solo la capa de panel/UI (Filament) y cada dominio vive en su propio paquete en `app-modules/`. `finance` seguiría esa misma convención como paquete nuevo y separado.
- El proyecto está decidido como **single-user por instancia** y **self-hosted/soberano** (sin intermediarios ni terceros obligatorios) a nivel general — ver "Decidido" en `vision.md`. Se asume que `finance` hereda esos mismos principios salvo que se decida lo contrario explícitamente (ver preguntas abiertas).
- `vault` tomó una decisión fuerte de cifrado zero-knowledge en cliente porque su contenido son credenciales. Datos financieros también son sensibles, pero de una naturaleza distinta (se necesita poder sumar, filtrar y graficar en servidor para los informes/comparativos) — **no se asume que `finance` deba replicar el mismo modelo de cifrado de `vault`**; es una pregunta abierta a resolver con calma, no una decisión ya tomada.

## Preguntas abiertas

Nada de esto está resuelto todavía. Son las preguntas que hay que cerrar antes de pasar a modelo de datos:

- **Contextos financieros**: ¿qué es exactamente? ¿personal vs. negocio, proyectos, presupuestos, centros de costo? Necesita definición concreta con ejemplos del propio usuario.
- **Cuentas**: ¿qué tipos (banco, efectivo, tarjeta de crédito, billetera digital)? ¿multi-moneda?
- **Transferencias**: ¿solo entre cuentas propias, o también incluye préstamos/deudas con terceros?
- **Categorización** de ingresos y gastos: ¿taxonomía fija o definida por el usuario?
- **"Oportunidades de ahorro"**: ¿qué significa en concreto? ¿presupuestos/límites por categoría, alertas, análisis de tendencia?
- **Recurrencia**: ¿hay ingresos/gastos recurrentes (nómina, suscripciones) que necesiten programarse o proyectarse?
- **Multi-usuario dentro de una instancia**: ¿es estrictamente una sola persona, o podría haber finanzas compartidas (pareja, familia) dentro del mismo modelo single-user del proyecto?
- **Nivel de privacidad/cifrado**: ¿los datos financieros requieren el mismo tratamiento zero-knowledge que `vault`, un nivel intermedio, o cifrado en reposo estándar? Pendiente de decidir con el mismo cuidado que se le dio a esa decisión en `vault`.
- **Soportes (documentos)**: aunque los informes tributarios son fase 2, ¿conviene empezar a capturar/adjuntar soportes (facturas, comprobantes) desde la fase 1 para no perder información retroactivamente, o eso también se pospone?

## Roadmap

Con el objetivo y la división de fases ya claros, el siguiente paso es cerrar las preguntas abiertas de arriba (especialmente contextos financieros, cuentas y qué significa "oportunidad de ahorro") antes de proponer un modelo de datos para `app-modules/finance`.
