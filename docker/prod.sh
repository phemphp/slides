#!/bin/sh
# Create or update the database in the volume, then run FrankenPHP.
set -e
mkdir -p var/data
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
# The new tables (post) have no migration: update the schema directly.
php bin/console doctrine:schema:update --force
# The data that the slides need (the post of the example slide).
php bin/console app:seed
exec frankenphp run --config Caddyfile
