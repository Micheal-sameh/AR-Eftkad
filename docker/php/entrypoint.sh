#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

if ! grep -q "^APP_KEY=base64" /var/www/html/.env; then
    php artisan key:generate --force
fi

echo "Waiting for database..."
until php artisan db:show > /dev/null 2>&1; do
    sleep 2
done

php artisan migrate --force

exec "$@"
