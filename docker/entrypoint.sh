#!/bin/sh
set -e

PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan migrate --force

if [ "$RUN_SEED" = "true" ]; then
  php artisan db:seed --force
fi

php artisan storage:link || true

exec apache2-foreground