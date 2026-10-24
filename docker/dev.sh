#!/bin/sh
# Install the dependencies, then run Tailwind and FrankenPHP in watch mode.
set -e
composer install --no-interaction
mkdir -p var/data
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
# The new tables (post) have no migration: update the schema directly.
php bin/console doctrine:schema:update --force
# Tailwind builds the CSS again when a class changes in the .phpm files.
php bin/console tailwind:build --watch &
# The workers restart when a PHP class in src/ changes.
export FRANKENPHP_WORKER_CONFIG='watch ./src/**/*.php'
# phemc builds again when a .phpm file changes, then restarts FrankenPHP.
exec vendor/bin/phemc watch src --exec "frankenphp run --config Caddyfile"
