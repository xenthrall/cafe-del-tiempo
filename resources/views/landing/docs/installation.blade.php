<x-landing::docs-layout
    title="Guía de instalación"
    description="Instala Café del Tiempo en tu propio servidor con Docker o de forma manual: requisitos, configuración del entorno, primer usuario, colas, HTTPS, respaldos y actualizaciones."
    :table-of-contents="[
        'elige-metodo' => 'Elige cómo instalar',
        'requisitos' => 'Requisitos',
        'docker' => 'Instalación con Docker',
        'manual' => 'Instalación manual',
        'configuracion' => 'Configuración del entorno',
        'primer-usuario' => 'Tu primer usuario',
        'procesos' => 'Colas y tareas programadas',
        'https' => 'Dominio y HTTPS',
        'respaldos' => 'Respaldos',
        'actualizar' => 'Actualizar',
        'problemas' => 'Problemas comunes',
    ]"
>
    <header class="flex flex-col gap-4">
        <p class="text-xs font-medium uppercase tracking-wider text-amber-700 dark:text-amber-400">Documentación</p>
        <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-stone-950 dark:text-stone-50">Guía de instalación</h1>
        <p class="text-base sm:text-lg text-stone-600 dark:text-stone-400 leading-relaxed">
            Instala {{ config('landing.name') }} en tu propio servidor, con Docker o de forma manual. Todo el código es abierto (MIT) y es el mismo que corre en la versión alojada.
        </p>
        <x-landing::callout variant="tip">
            ¿Solo quieres usarlo, sin servidores? Crea una cuenta gratis en <a href="{{ config('landing.hosted_url') }}" class="font-medium underline">{{ parse_url(config('landing.hosted_url'), PHP_URL_HOST) }}</a>.
        </x-landing::callout>
    </header>

    <x-landing::docs-section id="elige-metodo" title="Elige cómo instalar">
        <p>Hay dos caminos. Ambos instalan la misma aplicación; cambia cómo se ejecuta.</p>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-2 p-5 rounded-2xl border-2 border-amber-400/70 dark:border-amber-700/70 bg-white dark:bg-stone-900/60">
                <p class="text-xs font-medium uppercase tracking-wider text-amber-700 dark:text-amber-400">Recomendado para servidores</p>
                <p class="font-semibold text-stone-900 dark:text-stone-100">Docker</p>
                <p class="text-sm">PostgreSQL, Nginx, cola y tareas programadas vienen listos en <code>docker-compose.yml</code>. En el servidor solo necesitas Docker y Git.</p>
                <a href="#docker" class="text-sm">Instalar con Docker →</a>
            </div>
            <div class="flex flex-col gap-2 p-5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/40">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">Desarrollo o servidor propio</p>
                <p class="font-semibold text-stone-900 dark:text-stone-100">Manual</p>
                <p class="text-sm">Instalas PHP, Composer y Node.js tú mismo. Ideal para probar en tu máquina con SQLite o para servidores que ya tienen PHP.</p>
                <a href="#manual" class="text-sm">Instalar manualmente →</a>
            </div>
        </div>
    </x-landing::docs-section>

    <x-landing::docs-section id="requisitos" title="Requisitos">
        <h3>Con Docker</h3>
        <ul>
            <li><strong>Docker Engine</strong> con el plugin <strong>Docker Compose</strong> (el comando es <code>docker compose</code>, con espacio).</li>
            <li><strong>Git</strong>, para clonar y actualizar el repositorio.</li>
            <li>No necesitas PHP, Composer ni Node.js en el servidor: viven en las imágenes, y los assets compilados (<code>public/build</code>) vienen en el repositorio.</li>
        </ul>

        <h3>Instalación manual</h3>
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr><th>Componente</th><th>Versión</th><th>Notas</th></tr>
                </thead>
                <tbody>
                    <tr><td>PHP</td><td>8.3 o superior</td><td>Extensiones: <code>mbstring</code>, <code>bcmath</code>, <code>intl</code>, <code>gd</code>, <code>zip</code>, <code>exif</code>, <code>pcntl</code>, <code>pdo_sqlite</code> o <code>pdo_pgsql</code>, más las que Laravel trae por defecto (<code>ctype</code>, <code>fileinfo</code>, <code>openssl</code>, <code>tokenizer</code>, <code>xml</code>, <code>curl</code>).</td></tr>
                    <tr><td>Composer</td><td>2.x</td><td>Gestor de dependencias de PHP.</td></tr>
                    <tr><td>Node.js</td><td>20.19+ o 22.12+</td><td>Solo para compilar los assets con Vite.</td></tr>
                    <tr><td>Base de datos</td><td>SQLite 3 o PostgreSQL 16</td><td>SQLite viene por defecto y basta para uso personal. PostgreSQL es la opción para producción.</td></tr>
                </tbody>
            </table>
        </div>

        <h3>Servidor</h3>
        <p>Para uso personal o familiar basta un VPS pequeño: 1–2 vCPU, 2 GB de RAM y 10 GB de disco son una referencia orientativa. Si vas a abrir el registro a más personas, planea más memoria.</p>
    </x-landing::docs-section>

    <x-landing::docs-section id="docker" title="Instalación con Docker">
        <p>El stack levanta cinco servicios: <code>app</code> (PHP-FPM), <code>nginx</code> (expone el puerto <code>9000</code>), <code>postgres</code>, <code>queue</code> (worker de colas) y <code>scheduler</code> (tareas programadas).</p>

        <h3>1. Clona el repositorio</h3>
        <x-landing::code>git clone {{ config('landing.repository_url') }}.git
cd cafe-del-tiempo</x-landing::code>

        <h3>2. Crea el archivo de entorno</h3>
        <p>Usa la plantilla pensada para Docker, no <code>.env.example</code>:</p>
        <x-landing::code>cp .env.docker.example .env</x-landing::code>
        <p>Edita <code>.env</code> y ajusta al menos <code>APP_URL</code>, <code>DB_PASSWORD</code> y <code>APP_INSTANCE</code>. Tienes el detalle de cada variable en <a href="#configuracion">Configuración del entorno</a>.</p>

        <h3>3. Genera la clave de la aplicación</h3>
        <p>Genera la clave desde el host y pégala en <code>APP_KEY</code>. Así evitas problemas de permisos al escribir el <code>.env</code> desde el contenedor.</p>
        <x-landing::code>echo "base64:$(openssl rand -base64 32)"</x-landing::code>

        <h3>4. Construye y levanta los servicios</h3>
        <x-landing::code>docker compose build
docker compose up -d
docker compose ps</x-landing::code>
        <p>Al arrancar, el contenedor <code>app</code> espera a PostgreSQL y optimiza Laravel. Revisa que no haya errores con <code>docker compose logs -f app</code>.</p>

        <h3>5. Ejecuta las migraciones</h3>
        <p>Las migraciones no corren solas al arrancar, a propósito. Ejecútalas la primera vez y en cada actualización:</p>
        <x-landing::code>docker compose exec app php artisan migrate --force</x-landing::code>

        <h3>6. Crea tu usuario y entra</h3>
        <x-landing::code>docker compose exec app php artisan make:filament-user</x-landing::code>
        <p>Abre <code>http://TU_IP:9000/app</code> e inicia sesión. Para acceder también al panel de administración, sigue <a href="#primer-usuario">Tu primer usuario</a>.</p>

        <x-landing::callout variant="info" title="¿El puerto 9000 no responde?">
            Revisa el firewall del servidor (por ejemplo, <code>sudo ufw allow 9000/tcp</code>) o cambia el mapeo de puertos del servicio <code>nginx</code> en <code>docker-compose.yml</code>.
        </x-landing::callout>
    </x-landing::docs-section>

    <x-landing::docs-section id="manual" title="Instalación manual">
        <h3>En tu máquina (desarrollo o prueba)</h3>
        <p>Con SQLite no necesitas configurar ninguna base de datos:</p>
        <x-landing::code>git clone {{ config('landing.repository_url') }}.git
cd cafe-del-tiempo
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
npm run build
php artisan serve</x-landing::code>
        <p>Abre <code>http://localhost:8000/app</code>. Para desarrollar con recarga automática, usa <code>composer run dev</code> en lugar de <code>php artisan serve</code>.</p>

        <h3>En un servidor sin Docker</h3>
        <ol>
            <li>Apunta tu servidor web (Nginx, Apache o Caddy) a la carpeta <code>public/</code> del proyecto.</li>
            <li>Instala dependencias sin paquetes de desarrollo:
                <x-landing::code class="mt-2">composer install --no-dev --optimize-autoloader
npm ci && npm run build</x-landing::code>
            </li>
            <li>Configura el <code>.env</code> con <code>APP_ENV=production</code>, <code>APP_DEBUG=false</code> y tu base de datos (ver <a href="#configuracion">configuración</a>).</li>
            <li>Migra y optimiza:
                <x-landing::code class="mt-2">php artisan migrate --force
php artisan optimize</x-landing::code>
            </li>
            <li>Da permisos de escritura al usuario del servidor web sobre <code>storage/</code> y <code>bootstrap/cache/</code>.</li>
            <li>Configura el worker de colas y las tareas programadas, como se explica en <a href="#procesos">Colas y tareas programadas</a>.</li>
        </ol>
    </x-landing::docs-section>

    <x-landing::docs-section id="configuracion" title="Configuración del entorno">
        <p>Estas son las variables del <code>.env</code> que conviene revisar. El resto de valores de las plantillas ya funcionan tal cual.</p>
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr><th>Variable</th><th>Qué poner</th></tr>
                </thead>
                <tbody>
                    <tr><td><code>APP_URL</code></td><td>La URL pública real, con su esquema: <code>https://tu-dominio</code> o <code>http://TU_IP:9000</code>.</td></tr>
                    <tr><td><code>APP_KEY</code></td><td>Clave con la que Laravel firma sesiones y cookies. Genérala una vez y no la cambies: al hacerlo se cierran todas las sesiones.</td></tr>
                    <tr><td><code>APP_ENV</code> / <code>APP_DEBUG</code></td><td><code>production</code> y <code>false</code> en cualquier servidor accesible desde internet.</td></tr>
                    <tr><td><code>APP_INSTANCE</code></td><td><code>self-hosted</code> (por defecto): registro cerrado, las cuentas las crea un administrador. <code>hosted</code>: cualquiera puede registrarse.</td></tr>
                    <tr><td><code>APP_LOCALE</code></td><td><code>es</code> para la interfaz en español.</td></tr>
                    <tr><td><code>DB_CONNECTION</code> y <code>DB_*</code></td><td><code>sqlite</code> o <code>pgsql</code>. Con Docker deja <code>DB_HOST=postgres</code> y define una <code>DB_PASSWORD</code> fuerte.</td></tr>
                    <tr><td><code>SESSION_SECURE_COOKIE</code></td><td><code>true</code> si sirves la app por HTTPS.</td></tr>
                    <tr><td><code>QUEUE_CONNECTION</code></td><td><code>database</code>, ya configurado. Requiere un worker activo.</td></tr>
                    <tr><td><code>R2_PRIVATE_*</code></td><td>Credenciales del bucket privado de Cloudflare R2 (o compatible con S3) donde se guardan los respaldos.</td></tr>
                </tbody>
            </table>
        </div>

        <x-landing::callout variant="warning" title="Mantén el registro cerrado en instancias privadas">
            Con <code>APP_INSTANCE=hosted</code> cualquier persona que encuentre tu URL puede crear una cuenta. Si la instancia es para ti o tu familia, deja <code>self-hosted</code>.
        </x-landing::callout>
    </x-landing::docs-section>

    <x-landing::docs-section id="primer-usuario" title="Tu primer usuario">
        <p>Crea el usuario desde la terminal (en Docker, antepone <code>docker compose exec app</code>):</p>
        <x-landing::code>php artisan make:filament-user</x-landing::code>
        <p>Ese usuario entra al panel personal en <code>/app</code>. El panel de administración de la instancia, en <code>/system</code>, requiere además marcarlo como administrador:</p>
        <x-landing::code>php artisan tinker --execute 'App\Models\User::where("email", "tu@correo.com")->update(["is_admin" => true]);'</x-landing::code>
        <p>Desde <code>/system</code> puedes crear las cuentas del resto de personas sin abrir el registro público.</p>
    </x-landing::docs-section>

    <x-landing::docs-section id="procesos" title="Colas y tareas programadas">
        <p>La aplicación necesita dos procesos en segundo plano: un worker de colas y el programador de tareas, que ejecuta los respaldos diarios. Con Docker ya corren en los servicios <code>queue</code> y <code>scheduler</code>.</p>
        <p>En una instalación manual, mantén el worker activo con Supervisor o systemd:</p>
        <x-landing::code>php artisan queue:work --tries=3 --timeout=90</x-landing::code>
        <p>Y agrega el programador al cron del usuario del servidor web:</p>
        <x-landing::code title="crontab">* * * * * cd /ruta/a/cafe-del-tiempo && php artisan schedule:run >> /dev/null 2>&1</x-landing::code>
    </x-landing::docs-section>

    <x-landing::docs-section id="https" title="Dominio y HTTPS">
        <p>Pon un proxy inverso con HTTPS delante del puerto de la aplicación: Caddy, Nginx, Traefik o un túnel como Cloudflare Tunnel. La aplicación ya confía en las cabeceras <code>X-Forwarded-*</code> del proxy, así que solo tienes que:</p>
        <ol>
            <li>Poner <code>APP_URL=https://tu-dominio</code>.</li>
            <li>Poner <code>SESSION_SECURE_COOKIE=true</code>.</li>
            <li>Reiniciar: <code>docker compose up -d</code>.</li>
        </ol>
        <x-landing::callout variant="info">
            Si el navegador bloquea peticiones por "contenido mixto", casi siempre es porque <code>APP_URL</code> sigue con <code>http://</code>.
        </x-landing::callout>
    </x-landing::docs-section>

    <x-landing::docs-section id="respaldos" title="Respaldos">
        <p>La aplicación respalda la base de datos todos los días a las 02:00 y limpia los respaldos antiguos a la 01:30. Los archivos se suben al disco <code>r2_private</code>, así que necesitas configurar las variables <code>R2_PRIVATE_*</code>.</p>
        <ul>
            <li>Solo se respalda la base de datos: el código ya está en Git, y respaldar archivos incluiría el <code>.env</code> con tus credenciales.</li>
            <li>Guarda una copia del <code>.env</code> fuera del servidor: el respaldo no la incluye.</li>
            <li>Para probar la configuración, lanza un respaldo manual: <code>php artisan backup:run --only-db</code>.</li>
        </ul>
        <x-landing::callout variant="warning">
            Un respaldo que nunca restauraste es una suposición. Restaura uno en un entorno aparte de vez en cuando para confirmar que funciona.
        </x-landing::callout>
    </x-landing::docs-section>

    <x-landing::docs-section id="actualizar" title="Actualizar">
        <p>Con Docker:</p>
        <x-landing::code>git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force</x-landing::code>
        <p>En una instalación manual:</p>
        <x-landing::code>git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart</x-landing::code>
    </x-landing::docs-section>

    <x-landing::docs-section id="problemas" title="Problemas comunes">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr><th>Síntoma</th><th>Solución</th></tr>
                </thead>
                <tbody>
                    <tr><td><code>Unable to locate file in Vite manifest</code></td><td>Faltan los assets compilados. Ejecuta <code>npm run build</code> (o haz <code>git pull</code> si usas los del repositorio).</td></tr>
                    <tr><td>El contenedor <code>postgres</code> nunca queda "healthy"</td><td>Revisa que <code>DB_PASSWORD</code> no esté vacía en el <code>.env</code>.</td></tr>
                    <tr><td><code>key:generate</code> falla con permiso denegado en Docker</td><td>Genera la clave con <code>openssl</code> desde el host, como en el paso 3.</td></tr>
                    <tr><td>Cambios en el <code>.env</code> que no se aplican</td><td>La configuración está en caché. Ejecuta <code>php artisan optimize</code> o reinicia los contenedores.</td></tr>
                    <tr><td>No aparece la opción de registrarse</td><td>Es lo esperado con <code>APP_INSTANCE=self-hosted</code>. Crea las cuentas desde <code>/system</code>.</td></tr>
                </tbody>
            </table>
        </div>
        <p>¿Algo más? Abre un issue en <a href="{{ config('landing.repository_url') }}/issues" target="_blank" rel="noopener noreferrer">GitHub</a>.</p>
    </x-landing::docs-section>
</x-landing::docs-layout>
