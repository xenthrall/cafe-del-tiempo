# Vault — bóveda de credenciales, documento de planteamiento y decisiones

Documento vivo del módulo `vault`, mismo espíritu que [`vision.md`](vision.md): aquí se piensa en voz alta, se dejan preguntas abiertas y se debate hasta que algo se cierre de verdad. Todo lo específico de este módulo (qué es, cifrado, modelo de datos, preguntas abiertas, decisiones) vive aquí — `vision.md` queda para lo que aplica al proyecto completo, no solo a `vault`.

Última actualización: 2026-09-20.

## Qué es

Una bóveda de credenciales: contraseñas, códigos de recuperación, notas y datos sensibles. Es, en esencia, una caja fuerte — la prioridad número uno por encima de cualquier otra funcionalidad es la seguridad del dato en reposo y en tránsito. Es el primer módulo construido dentro de Café del Tiempo (ver [posicionamiento del proyecto](vision.md#posicionamiento-del-proyecto-decidido)), no la identidad completa del proyecto.

## MVP (decidido)

El primer entregable del módulo es la bóveda descrita arriba. Cápsulas del tiempo y cualquier otra idea quedan fuera del MVP hasta que la bóveda esté sólida (ver preguntas abiertas).

## Cifrado (decidido) — zero-knowledge real en cliente

**Decisión: Opción C.** Todo el cifrado y descifrado de los ítems de la bóveda ocurre en el cliente (navegador), con una clave derivada de la contraseña maestra vía Argon2id que nunca sale del cliente, y AES-256-GCM para cifrar. El servidor solo almacena y transporta blobs cifrados — nunca ve la contraseña maestra ni el texto plano, ni siquiera bajo un ataque activo al proceso del servidor.

Esto implica construir vistas propias para los ítems de la bóveda, fuera del CRUD server-rendered estándar de Filament. No es una desventaja a resolver ni un problema de encaje con Filament — es simplemente el costo de hacer zero-knowledge real, y se construirá como una experiencia custom y elegante, no como un parche.

Queda documentada abajo la comparación completa contra las otras dos opciones, como referencia de por qué se descartaron.

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

### Implicación arquitectónica (backend como almacén de blobs)

Dado que puede haber más de un cliente en el futuro, el backend se trata desde ya como una **API que solo mueve blobs cifrados** — el panel Filament actual sería simplemente el primer consumidor de esa API (aunque hoy viva en el mismo proceso Laravel, sin necesidad de una API HTTP separada todavía). Eso implica documentar el esquema de cifrado como protocolo propio, no como detalle de implementación de una sola pantalla — mismo formato de blob, mismos parámetros de KDF, para que cualquier cliente futuro sea compatible sin adivinar cómo lo hizo el primero.

### Comparación rápida

| | A. Servidor (clave de app) | B. Servidor (clave del usuario, en memoria) | C. Cliente (zero-knowledge) |
|---|---|---|---|
| Protege contra robo de backup/disco en frío | Parcial | Sí | Sí |
| Protege contra servidor comprometido en vivo | No | No | Sí |
| Se mantiene igual de segura sin importar qué cliente(s) se conecten después | No (la seguridad depende de confiar en el servidor, sea cual sea el cliente) | No (igual) | Sí (la seguridad no depende del servidor) |
| Requiere UI/lógica de cifrado a medida en cada cliente | No | No | Sí |
| Complejidad de implementación | Baja | Media | Alta |
| Es lo que usan los gestores de contraseñas serios hoy | No | Parcial | Sí |

Dado que la prioridad explícita es "el mecanismo más seguro posible, de verdad una caja fuerte" y que Filament no es una restricción permanente, la Opción C es la que responde a eso sin condiciones — las otras dos siguen dependiendo de confiar en el servidor, lo cual choca con "soberano" en su sentido más estricto. El costo real de la Opción C es tiempo de desarrollo, no arquitectura: hay que construir el cifrado en el cliente y tratar el backend como un almacén de blobs. Decisión cerrada: Opción C.

## Modelo de datos (decidido)

Con la Opción C ya decidida, el servidor nunca ve el contenido de un ítem — solo blobs cifrados. Eso cambia la pregunta: no es "¿qué columnas necesita un ítem?" sino "¿qué puede vivir en la base de datos en texto plano sin romper zero-knowledge, y qué debe viajar dentro del blob cifrado?". Esa distinción es el eje de todo lo que sigue.

### Decidido

- **Ítems: una sola entidad genérica**, no entidades separadas por tipo. `vault_items` con `id`, `type` en texto plano (enum: password, note, recovery_code, ...), `folder_id` (nullable), `encrypted_payload` (el blob — JSON cifrado con el resto de campos del ítem), `payload_schema_version` (para evolucionar el formato del blob sin romper ítems viejos), timestamps. Como el servidor nunca interpreta el contenido, modelar relacionalmente campos específicos por tipo no aporta nada — el servidor no puede validarlos ni indexarlos de todas formas. Es el mismo enfoque que Bitwarden/Vaultwarden con su entidad "cipher".
- **Etiquetas**: cifradas, embebidas como array dentro de `encrypted_payload` del ítem — sin tabla `tags` separada por ahora. El servidor nunca ve nombres de etiquetas. "Todas mis etiquetas" se resuelve en el cliente, agregando sobre los ítems ya descifrados en la sesión activa (viable en un vault single-user, no masivo). Si algún día hace falta autocompletar/reusar etiquetas de forma más sofisticada entre muchos ítems, se puede migrar a una tabla propia — no bloquea nada del MVP empezar así.
- **Carpetas**: `vault_folders` con `id` + `encrypted_name` cifrado (consistente con la lección de LastPass de abajo: ni el nombre de una carpeta debe quedar en plano) y `vault_items.folder_id` como FK en plano — el ID no revela contenido, solo estructura de agrupación. El campo de ícono para personalizarlas (p. ej. ícono de GitHub o de un servidor) se pospone, queda anotado como mejora futura.
- **Favoritos**: `is_favorite` en texto plano sobre `vault_items`. No expone contenido — como mucho revela qué ítems se usan más — y evita tener que descifrar todo el vault solo para filtrar favoritos.
- **Papelera / soft delete**: sí — los ítems eliminados no se borran de inmediato, `vault_items` usa `deleted_at`.
- **Adjuntos**: fuera del MVP. La visión general menciona "documentos", pero por ahora el alcance es credenciales/notas/códigos; adjuntos se retoma más adelante.
- **Historial de versiones de un ítem**: sí — se conserva el valor anterior al editar (útil para ver una contraseña rotada, por ejemplo). Modelo por definir: probablemente `vault_item_versions` con un snapshot del `encrypted_payload` cifrado por versión.
- **Búsqueda y listados**: el cliente descarga los ítems de la sesión activa y busca/filtra en memoria ya descifrados (modelo Bitwarden). No se construye índice cifrado searchable (blind index / HMAC determinístico) para el MVP — es complejidad que un vault single-user no necesita todavía; si el volumen de ítems lo justifica más adelante, se reevalúa.

Criterio detrás de estas cuatro últimas decisiones: al ser single-user por instancia (ver [modelo de usuarios](vision.md#decidido)), no hace falta la complejidad que sí tendría sentido en un producto multi-tenant (tablas de catálogo compartidas, índices searchable, etc.) — se elige lo más simple que no rompa zero-knowledge.

### La lección de LastPass aplica aquí

LastPass dejó metadatos (URLs) sin cifrar y eso fue lo que se filtró en su brecha de 2022. Cualquier campo que termine en plano en este modelo —nombre de carpeta, etiqueta, título del ítem, URL asociada— es candidato a fuga de metadata. Es el criterio que guió cada decisión "plano vs cifrado" de arriba.

## Autenticación (decidido, por ahora)

Se mantiene el login estándar de Filament sin cambios. 2FA/passkeys/clave maestra adicional quedan para más adelante, no es foco ahora mismo.

## Preguntas abiertas

Nada de esto está resuelto. Se van tachando o moviendo a "Decidido" a medida que se converse:

- **Protocolo de emergencia y rescate**: pausado a propósito, se retoma al final. No es prioridad ahora mismo — no diseñar nada sobre esto todavía aunque siga dentro del alcance declarado del MVP.
- **Estrategia de backup/recuperación de datos** (dentro del alcance, mecanismo por definir): cómo se generan respaldos seguros (exportación cifrada, snapshots de la base de datos, backups a almacenamiento externo/offline) para que un daño del servidor o pérdida de la máquina no signifique perder la bóveda. ¿Backup manual, automático, o ambos? ¿Dónde vive el respaldo si el proyecto es "sin intermediarios" (implica que no dependa de un tercero por defecto, aunque se podría permitir como opción del usuario)?
- **Cápsulas del tiempo y otras ideas futuras**: quedan fuera del MVP (ver arriba) — pendiente decidir si se retoman después o se descartan del todo.

## Decidido

- **2026-09-04 — Alcance del MVP**: bóveda de credenciales/contraseñas/códigos de recuperación/datos sensibles. La seguridad del dato es la prioridad sobre cualquier otra feature.
- **2026-09-04 — Alcance ampliado del MVP**: además de guardar datos, el MVP debe contemplar (aunque el diseño exacto siga abierto, ver arriba) un protocolo de emergencia/rescate y una estrategia de backup/recuperación ante daño o pérdida del servidor. No es opcional dejarlo para después: una caja fuerte sin forma de recuperarse ante un desastre no cumple su propósito.
- **2026-09-04 — Autenticación (por ahora)**: se mantiene el login estándar de Filament sin cambios. 2FA/passkeys/clave maestra adicional quedan para más adelante, no es foco ahora mismo.
- **2026-09-04 — Convención de módulo**: `vault` es un paquete/módulo separado de `app`, siguiendo la [convención modular general del proyecto](vision.md#decidido) — toda la lógica de la bóveda (modelos, migraciones, cifrado, resources de Filament propios) vive en `app-modules/vault`.
- **2026-09-13 — Cifrado**: Opción C — zero-knowledge real en el cliente (Argon2id + AES-256-GCM, cifrado/descifrado en el navegador). El servidor solo almacena y transporta blobs cifrados. Implica construir vistas propias para los ítems de la bóveda (fuera del CRUD estándar de Filament); no es un obstáculo sino el costo esperado de hacerlo bien — se construirá una experiencia custom y elegante. Ver [Cifrado (decidido) — zero-knowledge real en cliente](#cifrado-decidido--zero-knowledge-real-en-cliente).
- **2026-09-13 — Modelo de datos**: entidad genérica `vault_items` (`type` en plano, `folder_id`, `is_favorite` en plano, `encrypted_payload`, `payload_schema_version`). Etiquetas cifradas embebidas en el payload (sin tabla propia por ahora). Carpetas en `vault_folders` con `encrypted_name` cifrado y `folder_id` en plano como FK; el ícono de carpeta se pospone. Papelera vía soft delete. Historial de versiones por ítem (`vault_item_versions`, por definir en detalle). Adjuntos fuera del MVP. Búsqueda/listados resueltos en el cliente sobre ítems ya descifrados en la sesión activa, sin índice searchable. Ver [Modelo de datos (decidido)](#modelo-de-datos-decidido).

## Roadmap

Sin fases ni fechas todavía, pero con cifrado y modelo de datos ya decididos ya hay base suficiente para empezar a construir el módulo `vault` (migraciones, modelos, protocolo de cifrado en cliente). Las preguntas que quedan abiertas (protocolo de emergencia, backups, cápsulas del tiempo) siguen pausadas a propósito y no bloquean arrancar.
