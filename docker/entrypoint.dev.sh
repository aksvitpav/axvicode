#!/bin/sh
set -e

case "$CONTAINER_ROLE" in
    app)
        echo "CONTAINER_ROLE=app"
        if [ ! -d "vendor" ]; then
          composer install --no-interaction --prefer-dist
        fi

        if ! grep -q "APP_KEY=base64" .env && [ -z "$APP_KEY" ]; then
          php artisan key:generate
        fi

        php artisan migrate --force
        php artisan storage:link || true

        composer run dev -- --host 0.0.0.0 --watch --poll
        ;;
    supervisor)
        echo "CONTAINER_ROLE=supervisor"
        exec supervisord -c /etc/supervisor/supervisord.conf
        ;;
    *)
        echo "Unknown CONTAINER_ROLE"
        exit 1
        ;;
esac
