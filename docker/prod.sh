#!/bin/sh
# Create or update the database in the volume, then run FrankenPHP.
set -e
mkdir -p var/data
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
exec frankenphp run --config Caddyfile
