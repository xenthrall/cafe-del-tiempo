<section id="empezar" class="flex flex-col gap-8 max-w-2xl mx-auto w-full scroll-mt-24">
    <x-landing::section-heading eyebrow="Instalación" title="Comienza en tu servidor en minutos">
        Desarrollado sobre Laravel y PHP con soporte nativo para SQLite y PostgreSQL.
    </x-landing::section-heading>

    <x-landing::code title="Instalación rápida">
<span class="text-stone-500"># 1. Clona el proyecto y accede al directorio</span>
<span class="text-amber-400">git clone https://github.com/xenthrall/cafe-del-tiempo.git</span>
<span class="text-amber-400">cd cafe-del-tiempo</span>

<span class="text-stone-500"># 2. Instala dependencias y prepara el entorno</span>
composer install && npm install
cp .env.example .env && php artisan key:generate

<span class="text-stone-500"># 3. Migra la base de datos y compila activos</span>
php artisan migrate
npm run build

<span class="text-stone-500"># 4. Inicia tu suite personal</span>
<span class="text-emerald-400">php artisan serve</span></x-landing::code>

    <p class="text-center text-sm text-stone-600 dark:text-stone-400">
        ¿Vas a producción o prefieres Docker?
        <a href="{{ route('docs.installation') }}" class="font-medium text-amber-700 dark:text-amber-400 hover:underline">Lee la guía completa de instalación →</a>
    </p>
</section>
