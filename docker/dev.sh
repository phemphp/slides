#!/bin/sh
# Install the dependencies, then run Tailwind and FrankenPHP in watch mode.
set -e
# A tunnel makes this server public, and the secret of .env.dev is in git. Phem signs the state of
# the browser with the secret: make a random one for this machine (git ignores the file).
grep -qs '^APP_SECRET=' .env.dev.local \
    || printf 'APP_SECRET=%s\n' "$(od -An -tx1 -N32 /dev/urandom | tr -d ' \n')" >> .env.dev.local
composer install --no-interaction
mkdir -p var/data
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
# The new tables (post) have no migration: update the schema directly.
php bin/console doctrine:schema:update --force
# The data that the slides need (the post of the example slide).
php bin/console app:seed
# Tailwind builds the CSS again when a class changes in the .phem files.
php bin/console tailwind:build --watch &
# The workers restart when a PHP class in src/ changes.
export FRANKENPHP_WORKER_CONFIG='watch ./src/**/*.php'
# phemc builds again when a .phem file changes, then restarts FrankenPHP.
exec vendor/bin/phemc watch src --exec "frankenphp run --config Caddyfile"
