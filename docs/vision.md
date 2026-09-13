# Visión y Roadmap — documento vivo

Este archivo es el espacio de trabajo donde se debate y aterriza la idea del proyecto: qué es, qué no es, y en qué orden se va a construir. A diferencia del README, aquí sí se vale pensar en voz alta, dejar opciones abiertas y contradecirse mientras se decide. Cuando algo se cierre de verdad, se mueve a "Decidido" (o al README si es lo suficientemente estable).

Última actualización: 2026-09-04.

## MVP (decidido)

El primer entregable es **una bóveda de credenciales**: contraseñas, códigos de recuperación, notas y datos sensibles. Es, en esencia, una caja fuerte — la prioridad número uno por encima de cualquier otra funcionalidad es la seguridad del dato en reposo y en tránsito. Cápsulas del tiempo y cualquier otra idea quedan fuera del MVP hasta que la bóveda esté sólida.

## Punto de partida

Café del Tiempo es la idea de una bóveda digital privada, auto-alojada, para proteger y recuperar la información que más importa (credenciales, notas, documentos, mensajes). Es una idea personal en fase muy temprana: hay un esqueleto técnico (Laravel 13 + arquitectura modular vía `internachi/modular` + un módulo `app` con panel Filament v5), pero ningún dominio de negocio implementado todavía.

## Cómo está construido hoy (técnico, no producto)

- Laravel 13, PHP 8.3+.
- Modular: cada dominio será un paquete en `app-modules/`.
- `app-modules/app` (`tequia/app`): panel Filament v5 en `/app`, con login, sidebar/topbar propios y dashboard de bienvenida. Es solo andamiaje de UI, sin lógica de negocio.
- Tailwind v4 + Vite, Pest, Pint, SQLite por defecto.

## Preguntas abiertas

Nada de esto está resuelto. Se van tachando o moviendo a "Decidido" a medida que se converse:

- **Cifrado**: en discusión activa — ver la sección dedicada [Cifrado — opciones para decidir](#cifrado--opciones-para-decidir) más abajo.
- **Modelo de datos**: pausado a propósito. Antes de definir si son ítems genéricos con "tipo" o entidades separadas por tipo, hace falta plantear y explorar ideas de cómo se organiza la bóveda (categorías, favoritos, carpetas, etiquetas, etc.). No es prioridad todavía.
- **Protocolo de emergencia y rescate**: pausado a propósito, se retoma al final. No es prioridad ahora mismo — no diseñar nada sobre esto todavía aunque siga dentro del alcance declarado del MVP.
- **Estrategia de backup/recuperación de datos** (dentro del alcance, mecanismo por definir): cómo se generan respaldos seguros (exportación cifrada, snapshots de la base de datos, backups a almacenamiento externo/offline) para que un daño del servidor o pérdida de la máquina no signifique perder la bóveda. ¿Backup manual, automático, o ambos? ¿Dónde vive el respaldo si el proyecto es "sin intermediarios" (implica que no dependa de un tercero por defecto, aunque se podría permitir como opción del usuario)?
- **Cápsulas del tiempo y otras ideas futuras**: quedan fuera del MVP (ver arriba) — pendiente decidir si se retoman después o se descartan del todo.

## Cifrado — opciones para decidir

Esta es la decisión más importante y más urgente del proyecto: condiciona el modelo de datos, el frontend, los backups y qué tan cierto es realmente llamar "caja fuerte" a la bóveda. La pregunta de fondo no es "¿qué algoritmo usar?" (eso ya está resuelto por la industria: AES-256-GCM para cifrar, Argon2id para derivar claves de una contraseña). La pregunta real es **¿quién puede descifrar los datos, y en qué escenario de ataque siguen protegidos?**

Hay tres modelos posibles, de menos a más soberano/seguro. No son solo teóricos — cada uno corresponde a productos reales que existen hoy.

### Precisión importante: Filament es una capa de UI, no una restricción arquitectónica

Filament es la UI de hoy, no un compromiso permanente. La visión es que el backend sea el que de verdad importa: en el futuro podría haber otros clientes (una app móvil, un cliente CLI, una extensión de navegador, otro frontend en un stack distinto) consumiendo el mismo backend. Esto cambia el peso de las desventajas de cada opción:

- Que una opción "no encaje con el flujo nativo de Filament" deja de ser un problema real de arquitectura y pasa a ser, como mucho, más trabajo de UI hoy — un costo de una sola vez, no una limitación estructural.
- Si de verdad va a haber más de un cliente eventualmente, el backend **no debería depender de que exista un solo tipo de UI para funcionar de forma segura**. Un backend que es zero-knowledge no porque Filament lo obligue, sino porque así fue diseñado desde el core, es el que se mantiene igual de seguro sin importar cuántos clientes distintos se conecten después.
- Dicho de otra forma: la pregunta correcta no es "¿qué tan bien encaja esto con Filament?" sino "si mañana un cliente completamente distinto habla con este backend, ¿sigue siendo una caja fuerte real?". Solo la Opción C responde que sí sin condiciones.

### Opción A — Cifrado en servidor con la clave de la aplicación

Los datos se cifran/descifran en el backend (PHP) usando una clave que vive en el servidor — típicamente el `APP_KEY` de Laravel o una clave derivada de configuración, no del usuario. Es el cifrado "en reposo" que trae Laravel de fábrica (`Crypt` facade / cast `encrypted` en Eloquent, AES-256-CBC o GCM según versión).

- **Ventajas**: trivial de implementar (un cast de Eloquent), compatible al 100% con los formularios y tablas nativos de Filament sin JS adicional, permite búsqueda/filtrado server-side normal, los backups automáticos son simples porque el servidor siempre puede leer y volver a cifrar los datos.
- **Desventajas**: el servidor (y quien tenga acceso a él) siempre puede descifrar. No protege contra un servidor comprometido — si alguien roba la máquina, obtiene acceso root, o explota una vulnerabilidad de la app mientras corre, tiene la clave y los datos. No es lo que la industria llama "zero-knowledge".
- **Nivel de seguridad real hoy**: razonable para "cifrado en reposo" (protege si alguien roba solo el archivo de base de datos sin el servidor), pero es el eslabón más débil de las tres opciones. Es el modelo que usa la mayoría de apps admin normales, no el que usan los gestores de contraseñas serios.

### Opción B — Cifrado en servidor con clave derivada de tu contraseña maestra (envelope encryption)

El backend sigue cifrando/descifrando, pero la clave de cifrado no es fija ni vive en config: se deriva de tu contraseña maestra vía Argon2id en el momento del login, se mantiene solo en memoria/sesión mientras estás autenticado, y nunca se guarda en disco.

- **Ventajas**: mucho mejor que la Opción A contra robo de disco/backup en frío (sin la contraseña maestra activa, los datos son ilegibles), sigue siendo compatible con Filament sin reescribir el frontend, backups automáticos siguen siendo posibles (el proceso puede re-cifrar durante una sesión activa o con un mecanismo de clave de recuperación).
- **Desventajas**: si el servidor es comprometido *mientras* tienes sesión activa (proceso en memoria, ataque en vivo), la clave derivada está expuesta ese rato. Sigue siendo "el servidor puede ver tus datos", solo que no permanentemente.
- **Nivel de seguridad real hoy**: buen punto intermedio. Protege bien contra el escenario más común (robo de backup/disco offline), pero no contra un atacante activo en el servidor en tiempo real.

### Opción C — Cifrado en el cliente, zero-knowledge real (modelo Bitwarden / 1Password / Proton Pass / Vaultwarden)

Todo el cifrado y descifrado ocurre en el navegador (WebCrypto o una librería como libsodium.js), usando una clave derivada de tu contraseña maestra con Argon2id **en el cliente**. El servidor solo almacena y transporta blobs cifrados — nunca ve la contraseña maestra ni el texto plano, ni siquiera durante un ataque activo al proceso del servidor.

- **Ventajas**: es el estándar real de la industria para gestores de contraseñas serios (Bitwarden es open-source y auditado públicamente; Vaultwarden es una reimplementación self-hosted del mismo protocolo). Un servidor comprometido — incluso con acceso root en vivo — no expone los datos, porque el servidor nunca tuvo la clave. Es lo único que justifica con propiedad la palabra "zero-knowledge" y encaja perfecto con el posicionamiento "soberano, sin intermediarios" del proyecto. Además, al no depender de que el servidor entienda el contenido, el backend queda naturalmente listo para servir a cualquier cliente futuro (móvil, CLI, otro stack) sin rediseñar el modelo de seguridad — el trabajo de cifrado vive en cada cliente, no en Filament.
- **Desventajas**: exige construir vistas propias (fuera del CRUD server-rendered estándar) para los ítems de la bóveda, sea en Filament hoy o en cualquier otro cliente después — es un costo inherente a hacer zero-knowledge de verdad, no un problema específico de Filament. También complica el reseteo de contraseña maestra: si la pierdes sin una clave de recuperación guardada aparte, los datos son irrecuperables por diseño (esto conecta directo con la futura decisión de protocolo de emergencia, que dejamos pausada). Es notablemente más trabajo de implementación, y ese esquema de cifrado debe quedar bien documentado como un "protocolo" versionado (parámetros exactos de Argon2id, formato del blob cifrado, etc.) para que cualquier cliente futuro lo implemente igual — no puede quedar como lógica ad-hoc atada a un solo frontend.
- **Nivel de seguridad real hoy**: es el techo actual de lo que existe en la industria para este tipo de producto. Argon2id + AES-256-GCM en cliente es exactamente lo que usan los líderes del sector en 2025-2026. Caso real que ilustra por qué importa: la brecha de LastPass de 2022 expuso bóvedas cifradas robadas del backend — los atacantes no pudieron descifrar los ítems bien protegidos, pero sí pudieron ver metadatos que LastPass dejaba sin cifrar (URLs de sitios, por ejemplo). La lección no es solo "cifra en cliente", sino "cifra *todo* el campo, no solo lo obvio", sea cual sea la opción que se elija.

### Implicación arquitectónica si se elige la Opción C

Si el backend en algún momento va a tener más de un cliente, tiene sentido tratarlo desde ya como una **API que solo mueve blobs cifrados** — el panel Filament actual sería simplemente el primer consumidor de esa API (aunque hoy viva en el mismo proceso Laravel, sin necesidad de una API HTTP separada todavía). Eso implica documentar el esquema de cifrado como protocolo propio, no como detalle de implementación de una sola pantalla — mismo formato de blob, mismos parámetros de KDF, para que cualquier cliente futuro sea compatible sin adivinar cómo lo hizo el primero.

### Comparación rápida

| | A. Servidor (clave de app) | B. Servidor (clave del usuario, en memoria) | C. Cliente (zero-knowledge) |
|---|---|---|---|
| Protege contra robo de backup/disco en frío | Parcial | Sí | Sí |
| Protege contra servidor comprometido en vivo | No | No | Sí |
| Se mantiene igual de segura sin importar qué cliente(s) se conecten después | No (la seguridad depende de confiar en el servidor, sea cual sea el cliente) | No (igual) | Sí (la seguridad no depende del servidor) |
| Requiere UI/lógica de cifrado a medida en cada cliente | No | No | Sí |
| Complejidad de implementación | Baja | Media | Alta |
| Es lo que usan los gestores de contraseñas serios hoy | No | Parcial | Sí |

Dado que la prioridad explícita es "el mecanismo más seguro posible, de verdad una caja fuerte" y que Filament no es una restricción permanente, la Opción C es la que responde a eso sin condiciones — las otras dos siguen dependiendo de confiar en el servidor, lo cual choca con "soberano" en su sentido más estricto. El costo real de la Opción C es tiempo de desarrollo, no arquitectura: hay que construir el cifrado en el cliente y tratar el backend como un almacén de blobs. Sigue siendo tu decisión final — pero con este criterio, la balanza pesa claramente hacia C.

## Ideas en exploración

Espacio libre para anotar ideas sueltas sin comprometerse a nada. (Vacío por ahora — se va llenando en las próximas conversaciones.)

## Decidido

- **2026-09-04 — Alcance del MVP**: bóveda de credenciales/contraseñas/códigos de recuperación/datos sensibles. La seguridad del dato es la prioridad sobre cualquier otra feature.
- **2026-09-04 — Modelo de usuarios**: single-user. Cada instancia sirve a una sola persona (o unidad familiar de confianza), no es multi-tenant.
- **2026-09-04 — Modelo de despliegue**: 100% self-hosted y soberano. Sin intermediarios ni servicios de terceros obligatorios, sin telemetría ni rastreo de actividad. Corre en servidor propio o máquina local del usuario.
- **2026-09-04 — Alcance ampliado del MVP**: además de guardar datos, el MVP debe contemplar (aunque el diseño exacto siga abierto, ver arriba) un protocolo de emergencia/rescate y una estrategia de backup/recuperación ante daño o pérdida del servidor. No es opcional dejarlo para después: una caja fuerte sin forma de recuperarse ante un desastre no cumple su propósito.
- **2026-09-04 — Autenticación (por ahora)**: se mantiene el login estándar de Filament sin cambios. 2FA/passkeys/clave maestra adicional quedan para más adelante, no es foco ahora mismo.
- **2026-09-04 — Convención de módulos**: `vault` será un paquete/módulo nuevo y separado de `app`, ya que `app` es exclusivamente la capa de panel/UI (Filament) y no debe contener lógica de dominio. Toda la lógica de la bóveda (modelos, migraciones, cifrado, resources de Filament propios) vive en `app-modules/vault`.

## Roadmap

Sin fases ni fechas todavía — depende de que se cierren las preguntas abiertas de arriba, sobre todo alcance del MVP y estrategia de cifrado.
