#!/bin/sh
set -e

echo "Esperando a que PostgreSQL esté disponible en ${DB_HOST:-postgres}:${DB_PORT:-5432}..."

MAX_TRIES=30
TRIES=0

until PGPASSWORD="${DB_PASSWORD}" pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -q; do
  TRIES=$((TRIES + 1))
  if [ "$TRIES" -ge "$MAX_TRIES" ]; then
    echo "No se pudo conectar a PostgreSQL después de ${MAX_TRIES} intentos. Abortando."
    echo "Verifica DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD y que el usuario tenga permisos."
    exit 1
  fi
  sleep 2
done

echo "PostgreSQL disponible."

echo "Optimizando Laravel..."
php artisan optimize

echo "Ejecutando: $@"
exec "$@"