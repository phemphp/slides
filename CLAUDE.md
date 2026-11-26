# CLAUDE.md

Read AGENTS.md for the project rules. This file changes one of them.

## Database schema

Do not write migrations. Update the schema directly with
`bin/console doctrine:schema:update --force` after you change an entity. This replaces the
"schema changes go through migrations" rule of AGENTS.md.

- `docker/dev.sh` runs `doctrine:schema:update --force` at start, so each new database gets the
  tables.
- Seed the data with the fixtures: `bin/console doctrine:fixtures:load` (dev only; it empties
  all the tables first).
