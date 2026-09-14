# ==========================================================
# Etapa 1: Instalación de dependencias con Composer
# ==========================================================
FROM composer:2 AS vendor

WORKDIR /app

# Copiamos los manifiestos y app-modules/ antes de instalar: este proyecto
# usa path repositories (internachi/modular) para tequia/app y tequia/vault,
# así que Composer necesita esos directorios presentes en disco para
# resolverlos, no solo composer.json/composer.lock.
COPY composer.json composer.lock ./
COPY app-modules/ ./app-modules/

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Ahora sí copiamos el resto del proyecto (incluye app/, database/, y los
# assets ya compilados en public/build, subidos por ti).
COPY . .

# Generamos el autoloader optimizado ya con el código de la app presente.
# (No ejecutamos post-autoload-dump/package:discover aquí porque requiere
# un .env real; Laravel genera ese cache de forma perezosa en el primer
# request, y el entrypoint también lo fuerza al hacer config:cache).
RUN composer dump-autoload --no-dev --optimize

# ==========================================================
# Etapa 2: Imagen final de producción (PHP-FPM)
# ==========================================================
FROM php:8.3-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP requeridas por Laravel 13
RUN docker-php-ext-configure intl

RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Composer disponible dentro del contenedor (útil para debug/mantenimiento)
COPY --from=vendor /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

# Copiamos el proyecto completo (código + vendor) ya resuelto en la etapa anterior
COPY --from=vendor /app /var/www/html

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]