# Production image for Railway (also works with any Docker host).
# One image runs both services; APP_ROLE picks what a container does (see scripts/railway-start.sh):
#   web     the site, served by FrankenPHP (runs migrations and seeds the admin account on start)
#   worker  the queue worker that sends emails, plus the scheduler (daily privacy clean-up)

# ---------- Front-end assets (Vite) ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- PHP app ----------
FROM dunglas/frankenphp:1-php8.3 AS app

RUN install-php-extensions pdo_mysql gd intl zip exif bcmath pcntl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    COMPOSER_ALLOW_SUPERUSER=1

# Dependencies first, so they are cached between deploys that only change app code.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=5M\npost_max_size=8M\nmemory_limit=256M\nexpose_php=Off\nzend.exception_ignore_args=On\n' > "$PHP_INI_DIR/conf.d/app.ini"

CMD ["sh", "scripts/railway-start.sh"]
