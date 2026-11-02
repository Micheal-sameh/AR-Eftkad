#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    # The example file is for local development; never fall back to it with debug on.
    sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/; s/^APP_ENV=.*/APP_ENV=production/; s/^DEV_LOGIN=.*/DEV_LOGIN=false/' /var/www/html/.env
fi

if ! grep -q "^APP_KEY=base64" /var/www/html/.env; then
    php artisan key:generate --force
fi

echo "Waiting for database..."
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
    sleep 2
done

php artisan migrate --force

# Cache config/routes/views/events once per container start (env is final here).
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

exec "$@"
