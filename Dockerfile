# syntax=docker/dockerfile:1.7
FROM dunglas/frankenphp:1-php8.5 AS base
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*
# pcntl: vendor/bin/phemc gives the signals (Ctrl+C) to the compiler.
# intl: Symfony asks for it, for best performance.
RUN install-php-extensions intl pcntl pdo_sqlite
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app

# The project is mounted at /app. The presentation also uses this image: the deck shows the
# debug toolbar of Symfony.
FROM base AS dev
CMD ["docker/dev.sh"]
