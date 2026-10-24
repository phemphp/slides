# syntax=docker/dockerfile:1.7
FROM dunglas/frankenphp:1-php8.5 AS base
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*
# pcntl: vendor/bin/phemc gives the signals (Ctrl+C) to the compiler.
RUN install-php-extensions pcntl pdo_sqlite
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app

# --- Dev: the project is mounted at /app --------------------------------
FROM base AS dev
CMD ["docker/dev.sh"]

# --- Prod: the compiled application, for the live presentation ----------
FROM base AS prod
ENV APP_ENV=prod APP_DEBUG=0
COPY . /app
# composer install boots the kernel before phemc build: build its cache again after.
RUN printf 'APP_SECRET=%s\n' "$(od -An -tx1 -N32 /dev/urandom | tr -d ' \n')" > .env.local \
 && composer install --no-dev --optimize-autoloader --no-interaction \
 && vendor/bin/phemc build src --no-warmup \
 && rm -rf var/cache/prod \
 && php bin/console cache:warmup \
 && php bin/console tailwind:build --minify \
 && php bin/console asset-map:compile
COPY docker/php-prod.ini /usr/local/etc/php/conf.d/zz-app.ini
CMD ["docker/prod.sh"]
