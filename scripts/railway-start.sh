#!/bin/sh
# Container start for Railway. APP_ROLE picks what this service runs (default: web).
# Config, routes and views are cached at start, when Railway's variables are available.
set -e

ROLE="${APP_ROLE:-web}"

# Uploaded images live on the Railway volume mounted at storage/app/public; make sure its folders exist.
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

php artisan optimize --no-interaction

case "$ROLE" in
    web)
        php artisan storage:link --force --no-interaction
        php artisan migrate --force --no-interaction
        # Admin account (ADMIN_* variables) and site settings; only fills what is missing.
        php artisan db:seed --class=ProductionSeeder --force --no-interaction
        exec frankenphp php-server --root public/ --listen ":${PORT:-8080}"
        ;;
    worker)
        # The scheduler (daily privacy clean-up, failed-job pruning) runs alongside the queue worker,
        # which sends the queued emails and restarts hourly to free memory.
        php artisan schedule:work --no-interaction &
        exec php artisan queue:work --tries=5 --timeout=60 --sleep=3 --max-time=3600 --no-interaction
        ;;
    *)
        echo "Unknown APP_ROLE: $ROLE (use web or worker)" >&2
        exit 1
        ;;
esac
